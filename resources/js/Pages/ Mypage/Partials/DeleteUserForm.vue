<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({ password: '' })

const deleteAccount = () => {
  if (confirm('本当にアカウントを削除しますか？')) {
    form.delete(route('profile.destroy'))
  }
}
</script>

<template>
  <form @submit.prevent="deleteAccount" class="space-y-4">
    <div>
      <label class="block text-sm font-medium">パスワードを入力して削除</label>
      <input v-model="form.password" type="password" class="border rounded p-2 w-full" />
    </div>

    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded">
      アカウント削除
    </button>
  </form>
</template>
