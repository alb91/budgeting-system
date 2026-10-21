<template>
  <div class="container py-4">
    <div class="mb-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0" v-if="cycle">{{ t('expensesFor') }}: {{ capitalize(cycle.name) }}</h1>

        <div class="d-flex gap-2">
          <button class="btn btn-outline-secondary btn-sm" @click="changeLang"><i class="bi bi-translate"></i></button>
          <button class="btn btn-outline-secondary btn-sm" @click="goBack"><i class="bi bi-arrow-return-left"></i> {{ t('goBack') }}</button> 
          <button class="btn btn-success" @click="openCategories"><i class="bi bi-bookmark"></i> {{ t('categories')}}</button>
          <button class="btn btn-primary" @click="openModal"><i class="bi bi-cash"></i> {{ t('createExpense')}}</button>
        </div>
      </div>

      <div v-if="cycle" class="mt-2">
        <strong>{{ t('budget') }}:</strong> {{ formatCurrency(cycle.budget) }} |
        <strong>{{ t('spent') }}:</strong>  {{ formatCurrency(totalSpent) }} |
        <strong>{{ t('remaining') }}:</strong> {{ formatCurrency(remaining) }} |
      </div>
    </div>

    <div class="modal fade" tabindex="-1" v-if="showCategories" :class="{ show: showCategories }" style="display:block;">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-bookmark"></i> {{ t('categories') }}</h5>
            <button type="button" class="btn-close" @click="closeCategoriesModal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3 d-flex justify-content-end">
              <button class="btn btn-success btn-sm" @click="addCategoryRow">+ {{t('addCategory')}}</button>
            </div>
            <div v-for="(category, index) in categories" :key="index" class="row mb-3 align-items-center">

              <div class="col">
                <input type="text" class="form-control" :class="{ 'is-invalid': errorsCategories[index]?.name}" :placeholder="t('categoriesPlaceHolder')" v-model="category.name">
                <div v-if="errorsCategories[index]?.name" class="invalid-feedback d-block">{{ errorsCategories[index].name }}</div>
              </div>

              <div class="col">
                <input type="color" class="form-control form-control-color" :class="{ 'is-invalid': errorsCategories[index]?.color }" v-model="category.color" :title="t('choose')">
                <div v-if="errorsCategories[index]?.color" class="invalid-feedback d-block">{{ errorsCategories[index].color }}</div>
              </div>
              <div class="col-auto d-flex gap-1">
                <button v-if="categories.length > 1" class="btn btn-danger btn-sm" @click="removeCategoryRow(index, category.id, category.name)">-</button>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="closeCategoriesModal">{{ t('cancel') }}</button>
            <button class="btn btn-success" @click="saveCategories"><span v-if="savingSpinner" class="spinner-border spinner-border-sm me-2"></span>{{ savingSpinner ? `${t('saving')}` : `${t('save')}` }}</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" tabindex="-1" v-if="showForm" :class="{ show: showForm }" style="display:block;">
      <div class="modal-dialog modal-xl">
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

               <div class="col">
                <select class="form-select" :class="{ 'is-invalid': errors[index]?.selectedCategory }" v-model="entry.selectedCategory">
                  <option :value="''" disabled>{{ t('selectedCategory') }}</option>
                  <option v-for="category in originalCategories" :key="category.id" :value="category.id">
                    {{ capitalize(category.name) }}
                  </option>
                </select>
                <div v-if="errors[index]?.selectedCategory" class="invalid-feedback d-block">{{ errors[index].selectedCategory }}</div>
              </div>

              <div class="col-auto">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" v-model="entry.notify" :id="`notify-${index}`">
                  <label class="form-check-label" :for="`notify-${index}`">Notify</label>
                </div>
              </div>

              <div v-if="!isEditing" class="col-auto d-flex gap-1">
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
    <div class="modal-backdrop fade show" v-show="showForm || showCategories"></div>

    <SearchName v-model="searchFilter" :placeholder="t('search')"/>

    <div v-if="filteredItems.length" class="expenses">
      <table class="table table-hover mt-3">
        <thead class="table-light">
          <tr>
            <th>
              {{ t('name') }}
              <i v-if="sortDirection === 'asc' && sortBy === 'name'" @click="sortData('name', 'desc')" class="bi bi-arrow-down"></i>
              <i v-else @click="sortData('name', 'asc')" class="bi bi-arrow-up"></i>
            </th>
            <th>
              {{ t('date') }}
              <i v-if="sortDirection === 'asc' && sortBy === 'date'" @click="sortData('date', 'desc')" class="bi bi-arrow-down"></i>
              <i v-else @click="sortData('date', 'asc')" class="bi bi-arrow-up"></i>
            </th>
            <th>
              {{ t('category') }}
              <i v-if="sortDirection === 'asc' && sortBy === 'category'" @click="sortData('category', 'desc')" class="bi bi-arrow-down"></i>
              <i v-else @click="sortData('category', 'asc')" class="bi bi-arrow-up"></i>
            </th>
            <th>
              {{ t('amount') }}
              <i v-if="sortDirection === 'asc' && sortBy === 'amount'" @click="sortData('amount', 'desc')" class="bi bi-arrow-down"></i>
              <i v-else @click="sortData('amount', 'asc')" class="bi bi-arrow-up"></i>
            </th>
            <th>{{ t('actions') }}</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="expense in filteredItems" :key="expense.id">
            <td>{{ capitalize(expense.name) }}</td>
            <td>{{ expense.date }}</td>
            <td>{{ capitalize(getCategoryName(expense.category)) }}</td>
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
import SearchName from '@/components/SearchName.vue'


//translations
import es from '@/locales/es/expenses'
import en from '@/locales/en/expenses'

// helpers
import { sortTable } from '../helpers/sortTable';

//constants
const translations = { es, en }
const { formatCurrency, capitalize } = useFormatters()

const route = useRoute();
const router = useRouter();
const cycleId = route.params.id;
const cycle = ref(null);
const expenses = ref([]);
const errors = ref([]); 
const errorsCategories = ref([]); 

const columns = {
  name: {field: 'name', type: 'text'},
  date: {field: 'date', type: 'date'},
  category: {field: 'category', type: 'text'},
  amount: {field: 'amount', type: 'number'},
}


const totalSpent = computed(() => 
  expenses.value.reduce((sum, e) => sum + Number(e.amount), 0)
)

const remaining = computed(() => 
cycle.value ? cycle.value.budget - totalSpent.value : 0
)
//refs
const searchFilter = ref('');
const lang = ref('es')
const showForm = ref(false)
const showCategories = ref(false)
const entries = ref([
  { name: '', date: '',  amount: 0, selectedCategory: ''}
])
const categories = ref([
  { name: '', color: '', selectedCategory: ''}
]);

const originalCategories = ref([
  { name: '', color: '', selectedCategory: ''}
]);
const savingSpinner = ref(false); 

const isEditing = ref(false) 
const editingId = ref(null)
const sortDirection = ref(null); 
const sortBy = ref(null);

const sortData = (column, direction) => {
  sortDirection.value = direction;
  sortBy.value = column;

  expenses.value = sortTable(expenses.value, columns, column, direction);
};

const filteredItems = computed(() => {
  const query = searchFilter.value.trim().toLocaleLowerCase();

  if (!query) return expenses.value

  return expenses.value.filter((expense) => 
    expense.name.toLocaleLowerCase().includes(query)
  )
})



//lifecycle hooks
onMounted(() => {
  loadExpenses(); 
  loadCategories();
});

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

async function loadCategories() {
  try {
    const response = await api.get('/categories');
    categories.value = response.data;
    originalCategories.value = structuredClone(response.data);  
  } catch (error) {
    console.error(error); 
  }
}

// how can I make it so that the unsaved changes persist after a saved entry has been deleted? 
async function removeCategoryRow(index, id, name) {
  if (id) {
    const result = await Swal.fire({
      title: t('confirmDeleteTitle'),
      text: `${t('confirmCategoryDeleteText')}: "${name}"`,
      icon: 'warning',
      showCancelButton: true, 
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: t('confirmButtonText'),
      cancelButtonText: t('cancelButtonText')
    });

    if (!result.isConfirmed) return; 

    try {
      await api.delete(`/categories/${id}`);

      await loadCategories();

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
        text: t('errorMessage'),
        icon: 'error'
      });
    }
  } else {
    //  if the entry has not been saved, just removed it 
    categories.value.splice(index, 1);
  }
}

async function saveCategories() {
  errorsCategories.value = categories.value.map(() => ({
    name: null,
    color: null
  }));

  let hasError = false; 

  for (const [index, category] of categories.value.entries()) {
    if (!category.name.trim()) {
      errorsCategories.value[index].name = t('nameRequired');
      hasError = true; 
    }

    if (!category.color) {
      errorsCategories.value[index].color = t('colorRequired'); 
      hasError = true; 
    }
    
  }

  if (hasError) return; 

  savingSpinner.value = true; 

  try {
    for (const category of categories.value) { 
      if (category.id !== undefined) { 
        const matchedCategory = originalCategories.value.find(element => element.id === category.id);
        // we only update when we actually need to
        if(!matchedCategory || matchedCategory.name !== category.name || matchedCategory.color !== category.color || matchedCategory.selectedCategory !== category.selectedCategory){
          await api.put(`/categories/${category.id}`, {
            name: category.name,
            color: category.color
          });
        }
      } else {
        await api.post('/categories', {
          name: category.name,
          color: category.color
        });
      }
    }

    await loadCategories(); 

  } catch (error) {
    console.error(error); 

    Swal.fire({
      title: t('error'),
      text: t('errorMessage'),
      icon: 'error'
    });
  } finally {
    savingSpinner.value = false; 
  }
}

async function save() {
  errors.value = entries.value.map(() => ({
    name: null,
    date: null,
    amount: null,
    selectedCategory: null
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

     if (!entry.selectedCategory) {
      errors.value[index].selectedCategory = t('categoryRequired'); 
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
        category: entry.selectedCategory,
        notify: entry.notify ? 1 : 0
      });
    } else {
      for (const entry of entries.value) {
        await api.post('/expenses', {
          cycle_id: cycleId,
          name: entry.name, 
          date: entry.date,
          category: entry.selectedCategory,
          amount: entry.amount,
          notify: entry.notify ? 1 : 0,
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
      text: t('errorMessage'),
      icon: 'error'
    });
  }
}

//helpers
function t(key) { 
 return translations[lang.value][key];  
}

function changeLang(){
  if(lang.value === 'es') {
    lang.value = 'en'
  } else {
    lang.value = 'es'
  }
}

function getCategoryName(id){
  if(id){
    const category = categories.value.find(category => Number(category.id) === Number(id));
    return category ? category.name : '-';    
  } 
}

function addRow() {
  entries.value.push({ name: '', date: '', amount: 0, selectedCategory: '', notify: 0 });
  errors.value.push({ name: null, date: null, amount: null, selectedCategory: null });
}

function addCategoryRow() {
  categories.value.push({ name: '', color: ''});
}

function removeRow(index) {
  entries.value.splice(index, 1)
}

function openModal() {
  showForm.value = true; 

  errors.value = entries.value.map(() => ({
    name: null,
    date: null, 
    amount: null,
    selectedCategory: null
  }));
}

async function openCategories(){
  await loadCategories();
  showCategories.value = true; 

  errorsCategories.value = categories.value.map(() => ({
    name: null,
    color: null
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
    selectedCategory: '',
    notify: 0
  }]; 

  errors.value = [];
}

function closeCategoriesModal(){
  showCategories.value = false; 

  errorsCategories.value = []; 
}

function editExpense(expense) {
  isEditing.value = true; 
  editingId.value = expense.id; 

  entries.value = [{
    name: expense.name,
    date: expense.date,
    amount: Number(expense.amount),
    selectedCategory: expense.category,
    notify: Boolean(expense.notify)
  }];

  showForm.value = true; 
}

function goBack() {
  router.push({ name: 'cycles' }); 
}

</script>