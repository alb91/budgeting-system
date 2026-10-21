<script setup>

defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    placeholder: {
        type: String,
        default: 'Search'
    }
});

const emit = defineEmits(['update:modelValue']);

</script>
<template>
    <nav class="navbar navbar-light bg-light">
        <div class="container-fluid">
            <form class="d-flex" @submit.prevent>
                <input class="form-control me-2" type="search" :value="modelValue" :placeholder="placeholder" aria-label="Search" @input="emit('update:modelValue', $event.target.value)">
            </form>
        </div>
    </nav>
</template>
