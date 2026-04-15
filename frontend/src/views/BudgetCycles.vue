<template>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="mb-0">{{ t('title') }}</h1>

      <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary btn-sm" @click="lang = 'es'">ES</button>
        <button class="btn btn-outline-secondary btn-sm" @click="lang = 'en'">EN</button>
      </div>

      <button class="btn btn-primary" @click="openModal">{{ t('createCycle')}}</button>
    </div>
    

    <div class="modal fade" tabindex="-1" v-if="showForm" :class="{ show: showForm }" style="display:block;">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditing ? t('editCycle') : t('newCycle') }}</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <input type="text" class="form-control" :placeholder="t('namePlaceHolder')" :class="{ 'is-invalid': errors.name }" v-model="name">
              <div class="invalid-feedback"> {{ errors.name }} </div>
            </div>
            <div class="mb-3">
              <input type="date" class="form-control" :class="{ 'is-invalid': errors.startDate }" v-model="startDate">
              <div class="invalid-feedback"> {{ errors.startDate }} </div>
            </div>
            <div class="mb-3">
              <input type="date" class="form-control" :class="{ 'is-invalid': errors.endDate }" v-model="endDate">
              <div class="invalid-feedback"> {{ errors.endDate }} </div>
            </div>
            <div class="mb-3">
              <input type="text" class="form-control" :class="{ 'is-invalid': errors.budget }" :value="budgetDisplay" @input="onBudgetInput" :placeholder="t('budgetPlaceHolder')">
              <div class="invalid-feedback"> {{ errors.budget }} </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="closeModal">{{ t('cancel') }}</button>
            <button class="btn btn-success" @click="save"> {{ t('save' )}}</button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show" v-show="showForm"></div>

    <div v-if="cycles.length" class="cycles">
      <h2>{{ t('cycles') }}</h2>
      <table class="table table-hover mt-3">
        <thead class="table-light">
          <tr>
            <th>{{ t('name') }}</th>
            <th>{{ t('start') }}</th>
            <th>{{ t('end') }}</th>
            <th>{{ t('budget') }}</th>
            <th>{{ t('spent') }}</th>
            <th>{{ t('remaining') }}</th>
            <th>{{ t('actions') }}</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="cycle in cycles" :key="cycle.id">
            <td>{{ capitalize(cycle.name) }}</td>
            <td>{{ cycle.start_date }}</td>
            <td>{{ cycle.end_date }}</td>
            <td>{{ formatCurrency(cycle.budget) }}</td>
            <td>{{ formatCurrency(cycle.spent) }}</td>
            <td>{{ formatCurrency(cycle.remaining) }}</td>
            <td>
              <div class="d-flex gap-1 justify-content-center">
                <button class="btn btn-sm btn-outline-primary" @click="addExpenses(cycle.id)" :title="t('addExpenses')">
                  <i class="bi bi-plus-circle"></i>
                </button>
                <button class="btn btn-sm btn-outline-warning" @click="editCycle(cycle)" :title="t('edit')">
                  <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" @click="deleteCycle(cycle.id)" :title="t('delete')">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-else>
      {{  t('noCycles') }}
    </div>
  </div>
</template>

<script setup>
//imports
import { ref, onMounted, computed, reactive } from 'vue'
import { useFormatters } from '@/composables/useFormatters'
import { useRouter } from 'vue-router'
import api from '@/api/http'
import Swal from 'sweetalert2'

//translations
import es from '@/locales/es/budgetCycles'
import en from '@/locales/en/budgetCycles'

//constants
const translations = { es, en }
const { formatCurrency, capitalize } = useFormatters()
const router = useRouter()

//refs
const lang = ref('es')
const cycles = ref([])

const showForm = ref(false)
const name = ref('')
const startDate = ref('')
const endDate = ref('')
const budget = ref(0)
const spent = ref(0)
const remaining = ref(0)
const isEditing = ref(false)
const editingId = ref(null)

const errors = reactive({
  name: null,
  startDate: null,
  endDate: null,
  budget: null
});

const budgetDisplay = computed(() => {
  return budget.value ? new Intl.NumberFormat('es-MX').format(budget.value) : ''
}); 

//lifecycle hooks
onMounted(loadCycles)

//main functions
async function loadCycles() {
  try {
    const response = await api.get('/budget-cycles')
    cycles.value = response.data; 
  } catch (error) {
    console.error(error); 
  }
}

async function deleteCycle(id) {
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
    await api.delete(`/budget-cycles/${id}`); 

    await loadCycles(); 

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

async function save() {

  errors.name = null;
  errors.startDate = null; 
  errors.endDate = null; 
  errors.budget = null; 

  let hasError = false; 

  if (!name.value.trim()) {
    errors.name = t('nameRequired'); 
    hasError = true; 
  }

  if (!startDate.value) {
    errors.startDate = t('startDateRequired'); 
    hasError = true; 
  }

  if (!endDate.value) {
    errors.endDate = t('endDateRequired'); 
    hasError = true; 
  }

  if (!budget.value || budget.value <= 0) {
    errors.budget = t('budgetInvalid'); 
    hasError = true; 
  }

  if (hasError) return; 

  try {
    if (isEditing.value) {
      await api.put(`/budget-cycles/${editingId.value}`, {
        name: name.value,
        start_date: startDate.value,
        end_date: endDate.value,
        budget: budget.value
      });
    } else {
      await api.post('/budget-cycles', {
        name: name.value,
        start_date: startDate.value,
        end_date: endDate.value,
        budget: budget.value
      });
    }

    await loadCycles(); 

    name.value = ''
    startDate.value = ''
    endDate.value = ''
    budget.value = 0
  
    closeModal()

  } catch (error){
    //asegurarme de seguir respuestas con códigos correctos
    console.error('Error:', error.response ? error.response.data : error); 
  }
}

//helpers
function t(key) {
 return translations[lang.value][key];  
}

function addExpenses(id) {
    router.push({
        name: 'expenses',
        params: { id }
    });
}

function openModal() {
  showForm.value = true;
}

function closeModal() {
  showForm.value = false;
  isEditing.value = false; 
  editingId.value = null; 

  name.value = '';
  startDate.value = ''; 
  endDate.value = ''; 
  budget.value = 0; 

  errors.name = null; 
  errors.startDate = null; 
  errors.endDate = null; 
  errors.budget = null; 
}

function onBudgetInput(e) {
  const numeric = e.target.value.replace(/[^\d.]/g, '');
  budget.value = numeric ? Number(numeric) : 0;
}

function editCycle(cycle) {
  isEditing.value = true; 
  editingId.value = cycle.id

  name.value = cycle.name; 
  startDate.value = cycle.start_date; 
  endDate.value = cycle.end_date; 
  budget.value = Number(cycle.budget); 

  showForm.value = true; 
}


</script>
