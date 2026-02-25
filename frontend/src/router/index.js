import { createRouter, createWebHistory } from 'vue-router'
import BudgetCycles from '@/views/BudgetCycles.vue'
import Expenses from '@/views/Expenses.vue'

const routes = [
    {
        path: '/',
        name: 'cycles',
        component: BudgetCycles
    },
    {
        path: '/cycles/:id/expenses',
        name: 'expenses',
        component: Expenses,
        props: true
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
});

export default router