<template>
  <div class="container py-4">
    <div class="mb-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0" v-if="cycle">{{ t('expensesFor') }}: {{ capitalize(cycle.name) }}</h1>

        <div class="d-flex gap-2">
          <button class="btn btn-outline-secondary btn-sm" @click="lang = 'es'">ES</button>
          <button class="btn btn-outline-secondary btn-sm" @click="lang = 'en'">EN</button>
          <button class="btn btn-outline-secondary btn-sm" @click="goBack"> ← {{ t('goBack') }}</button> 
          <button class="btn btn-primary" @click="openModal">{{ t('createExpense')}}</button>
        </div>
      </div>

      <div v-if="cycle" class="mt-2">
        <strong>{{ t('budget') }}:</strong> {{ formatCurrency(cycle.budget) }} |
        <strong>{{ t('spent') }}:</strong>  {{ formatCurrency(totalSpent) }} |
        <strong>{{ t('remaining') }}:</strong> {{ formatCurrency(remaining) }} |
      </div>
    </div>

    <div class="modal fade" tabindex="-1" v-if="showForm" :class="{ show: showForm }" style="display:block;">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditing ? t('editExpense') : t('newExpense') }}</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <input type="text" class="form-control" :placeholder="t('expensePlaceHolder')" :class="{ 'is-invalid': errors.name }" v-model="name">
              <div class="invalid-feedback"> {{ errors.name }} </div>
            </div>
            <div class="mb-3">
              <input type="date" class="form-control" :class="{ 'is-invalid': errors.date }" v-model="date">
              <div class="invalid-feedback"> {{ errors.date }} </div>
            </div>
            <div class="mb-3">
              <input type="text" class="form-control" :class="{ 'is-invalid': errors.amount }" :value="amountDisplay" @input="onAmountInput" :placeholder="t('amountPlaceHolder')">
              <div class="invalid-feedback">{{ errors.amount }} </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="closeModal">{{ t('cancel') }}</button>
            <button class="btn btn-success" @click="save">{{ t('save') }}</button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show" v-show="showForm"></div>

    <div v-if="expenses.length" class="expenses">
      <table class="table table-hover mt-3">
        <thead class="table-light">
          <tr>
            <th>{{ t('name') }}</th>
            <th>{{ t('date') }}</th>
            <th>{{ t('amount') }}</th>
            <th>{{ t('actions') }}</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="expense in expenses" :key="expense.id">
            <td>{{ capitalize(expense.name) }}</td>
            <td>{{ expense.date }}</td>
            <td>{{ formatCurrency(expense.amount) }}</td>
            <td>
              <div class="dropdown">
                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  {{ t('actions')  }}
                </button>
                <ul class="dropdown-menu">
                  <li>
                    <button class="dropdown-item" @click="editExpense(expense)">{{ t('edit') }}</button>
                  </li>
                  <li>
                    <button class="dropdown-item text-danger" @click="deleteExpense(expense.id)">{{ t('delete') }}</button>
                  </li>
                </ul>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-else>
        <p>No expenses registered for this cycle yet.</p>
    </div>
  </div>
</template>

<script setup>
//imports
import { ref, onMounted, computed, reactive } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/api/http'
import Swal from 'sweetalert2'

//translations
import es from '@/locales/es/expenses'
import en from '@/locales/en/expenses'

//constants
const translations = { es, en }

const route = useRoute()
const router = useRouter()
const cycleId = route.params.id
const cycle = ref(null)
const expenses = ref([])

const errors = reactive({
  name: null,
  date: null,
  amount: null
});

const amountDisplay = computed(() => {
  return amount.value ? new Intl.NumberFormat('es-MX').format(amount.value) : ''
}); 

const totalSpent = computed(() => 
  expenses.value.reduce((sum, e) => sum + Number(e.amount), 0)
)

const remaining = computed(() => 
cycle.value ? cycle.value.budget - totalSpent.value : 0
)
//refs
const lang = ref('es')
const showForm = ref(false)
const name = ref('')
const date = ref('')
const amount = ref(0)
const isEditing = ref(false) 
const editingId = ref(null)

//lifecycle hooks
onMounted(loadExpenses)

//main functions
async function loadExpenses() {
    try {
        const response = await api.get(`cycles/${cycleId}/expenses`)
        cycle.value = response.data.cycle
        expenses.value = response.data.expenses; 
    } catch (error) {
        console.error(error); 
    }
}

async function save() {

  errors.name = null; 
  errors.date = null; 
  errors.amount = null; 

  let hasError = false; 

  if (!name.value.trim()) {
    errors.name = t('nameRequired'); 
    hasError = true; 
  }

  if (!date.value) {
    errors.date = t('dateRequired'); 
    hasError = true; 
  }

  if (!amount.value || amount.value <= 0) {
    errors.amount = t('amountInvalid'); 
    hasError = true; 
  }

  if (hasError) return; 

  try {
    if (isEditing.value) {
      await api.put(`/expenses/${ editingId.value }`, {
        name: name.value,
        date: date.value,
        amount: amount.value
      });
    } else {
      await api.post('/expenses', {
        cycle_id: cycleId,
        name: name.value, 
        date: date.value,
        amount: amount.value
      });
    }

    await loadExpenses(); 

    name.value = ''
    date.value = ''
    amount.value = ''

    closeModal(); 
  } catch (error) {
    console.error('Error:', error.response ? error.response.data : error); 
  }
}

async function deleteExpense(id) {
  const result = await Swal.fire({
    title: t('confirmDeleteTitle'),
    text: t('confirmDeleteText'),
    icon: 'warning',
    showCancelButton: true, 
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: t('confirmButtonText'),
    cancelButtonText: t('cancelButtonText')
  });

  if (!result.isConfirmed) return; 

  try {
    await api.delete(`/expenses/${id}`); 

    await loadExpenses(); 

    await Swal.fire({
      title: t('deleted'),
      text: t('deleteSuccess'),
      icon: 'success',
      timer: 1500,
      showConfirmButton: false
    });
  } catch (error) {
    console.error(error); 

    Swal.fire({
      title: t('error'),
      text: t('deleteError'),
      icon: 'error'
    });
  }
}

//helpers
function t(key) {
 return translations[lang.value][key];  
}

function onAmountInput(e) {
  const numeric = e.target.value.replace(/[^\d]/g, '');
  amount.value = numeric ? Number(numeric) : 0;
}

function openModal() {
  showForm.value = true; 
}

function closeModal() {
  showForm.value = false; 
  isEditing.value = false; 
  editingId.value = null; 

  name.value = '';
  date.value = ''; 
  amount.value = ''; 

  errors.name = null; 
  errors.date = null; 
  errors.amount = null; 
}

function capitalize(str) {
  if (!str) return ''; 
  return str.charAt(0).toUpperCase() + str.slice(1); 
}

function formatCurrency(amount) {
  if (amount === null || amount === undefined) return '$0.00';

  return new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN',
    minimumFractionDigits: 2
  }).format(amount);
}

function editExpense(expense) {
  isEditing.value = true; 
  editingId.value = expense.id; 

  name.value = expense.name,
  date.value = expense.date,
  amount.value = expense.amount

  showForm.value = true; 
}

function goBack() {
  router.push({ name: 'cycles' }); 
}

</script>

<!-- Hay que segmentar los helpers y demás cosas repetidas, posiblemente lo de las traducciones
 comenzar con el crud para expenses 
 -->