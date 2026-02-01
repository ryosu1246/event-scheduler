<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  name: '',
  email: '',
})

const submit = () => {
  form.put(route('profile.update'))
}
</script>

<template>
  <form @submit.prevent="submit" class="space-y-4">
    <div>
      <label class="block text-sm font-medium">名前</label>
      <input v-model="form.name" type="text" class="border rounded p-2 w-full" />
    </div>

    <div>
      <label class="block text-sm font-medium">メールアドレス</label>
      <input v-model="form.email" type="email" class="border rounded p-2 w-full" />
    </div>

    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
      保存
    </button>
  </form>
</template>
