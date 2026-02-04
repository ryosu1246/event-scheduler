<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.put(route('password.update'))
}
</script>

<template>
  <form @submit.prevent="submit" class="space-y-4">
    <div>
      <label class="block text-sm font-medium">現在のパスワード</label>
      <input v-model="form.current_password" type="password" class="border rounded p-2 w-full" />
    </div>

    <div>
      <label class="block text-sm font-medium">新しいパスワード</label>
      <input v-model="form.password" type="password" class="border rounded p-2 w-full" />
    </div>

    <div>
      <label class="block text-sm font-medium">確認用パスワード</label>
      <input v-model="form.password_confirmation" type="password" class="border rounded p-2 w-full" />
    </div>

    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
      更新
    </button>
  </form>
</template>
