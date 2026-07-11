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
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditing ? t('editExpense') : t('newExpense') }}</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <div v-for="(entry, index) in entries" :key="index" class="row mb-3 align-items-center">

              <div class="col">
                <input type="text" class="form-control" :class="{ 'is-invalid': errors[index]?.name }" :placeholder="t('expensePlaceHolder')" v-model="entry.name">
                <div v-if="errors[index]?.name" class="invalid-feedback d-block">{{ errors[index].name }}</div>
              </div>

              <div class="col">
                <input type="date" class="form-control" :class="{ 'is-invalid': errors[index]?.date }" v-model="entry.date">
                <div v-if="errors[index]?.date" class="invalid-feedback d-block">{{ errors[index].date }}</div>
              </div>

              <div class="col">
                <input type="number" step="0.01" class="form-control" :class="{ 'is-invalid': errors[index]?.amount }" v-model.number="entry.amount">
                <div v-if="errors[index]?.amount" class="invalid-feedback d-block">{{ errors[index].amount }}</div>
              </div>

              <div class="col-auto">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" v-model="entry.notify" :id="`notify-${index}`">
                  <label class="form-check-label" :for="`notify-${index}`">Notify</label>
                </div>
              </div>

              <div class="col-auto d-flex gap-1">
                <button v-if="entries.length > 1" class="btn btn-danger btn-sm" @click="removeRow(index)">-</button>
                
                <button class="btn btn-success btn-sm" @click="addRow">+</button>
              </div>

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
                <button class="btn btn-sm btn-outline-warning" @click="editExpense(expense)" :title="t('edit')">
                  <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" @click="deleteExpense(expense.id)" :title="t('delete')">
                  <i class="bi bi-trash"></i>
                </button>
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
import { useFormatters } from '@/composables/useFormatters'
import { useRoute, useRouter } from 'vue-router'
import api from '@/api/http'
import Swal from 'sweetalert2'

//translations
import es from '@/locales/es/expenses'
import en from '@/locales/en/expenses'

//constants
const translations = { es, en }
const { formatCurrency, capitalize } = useFormatters()

const route = useRoute();
const router = useRouter();
const cycleId = route.params.id;
const cycle = ref(null);
const expenses = ref([]);
const errors = ref([]); 

const totalSpent = computed(() => 
  expenses.value.reduce((sum, e) => sum + Number(e.amount), 0)
)

const remaining = computed(() => 
cycle.value ? cycle.value.budget - totalSpent.value : 0
)
//refs
const lang = ref('es')
const showForm = ref(false)
const entries = ref([
  { name: '', date: '',  amount: 0}
])
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

  errors.value = entries.value.map(() => ({
    name: null,
    date: null,
    amount: null
  }));

  let hasError = false; 

  for (const [index, entry] of entries.value.entries()) {
    if (!entry.name.trim()) {
      errors.value[index].name = t('nameRequired');
      hasError = true;
    }

    if (!entry.date) {
      errors.value[index].date = t('dateRequired');
      hasError = true; 
    }

    if (!entry.amount || entry.amount <= 0) {
      errors.value[index].amount = t('amountInvalid'); 
      hasError = true; 
    }
  }

  if (hasError) return; 
  
  try {
    if (isEditing.value) {

      const entry = entries.value[0]; 

      await api.put(`/expenses/${ editingId.value }`, {
        name: entry.name,
        date: entry.date,
        amount: entry.amount, 
        notify: entry.notify
      });
    } else {
      for (const entry of entries.value) {
        await api.post('/expenses', {
          cycle_id: cycleId,
          name: entry.name, 
          date: entry.date,
          amount: entry.amount,
          notify: entry.notify ?? 0,
        });
      }
    }

    await loadExpenses(); 

    entries.value = [
      {
        name: '',
        date: '',
        amount: 0,
        notify: 0
      }
    ];

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

function addRow() {
  entries.value.push({ name: '', date: '', amount: 0, notify: 0 });
  errors.value.push({ name: null, date: null, amount: null });
}

function removeRow(index) {
  entries.value.splice(index, 1)
}

function onAmountInput(e, index) {
  const numeric = e.target.value.replace(/[^\d.]/g, '');

  entries.value[index].amount =  numeric ? Number(numeric) : 0;
}

function openModal() {
  showForm.value = true; 

  errors.value = entries.value.map(() => ({
    name: null,
    date: null, 
    amount: null
  }));
}

function closeModal() {
  showForm.value = false; 
  isEditing.value = false; 
  editingId.value = null; 

  entries.value = [{
    name: '', 
    date: '',
    amount: 0,
    notify: 0
  }]; 

  errors.value = [];
}

function editExpense(expense) {
  isEditing.value = true; 
  editingId.value = expense.id; 

  entries.value = [{
    name: expense.name,
    date: expense.date,
    amount: Number(expense.amount),
    notify: Boolean(expense.notify)
  }];

  showForm.value = true; 
}

function goBack() {
  router.push({ name: 'cycles' }); 
}

</script>