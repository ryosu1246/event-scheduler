<script setup>
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue'
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue'
import DeleteUserForm from './Partials/DeleteUserForm.vue'

defineProps({
  mustVerifyEmail: Boolean,
  status: String,
});
</script>

<template>
  <div class="max-w-2xl mx-auto py-8 space-y-8">
    <h2 class="text-2xl font-bold mb-6">マイページ</h2>

    <section>
      <h3 class="text-lg font-semibold mb-2">プロフィール情報</h3>
      <UpdateProfileInformationForm />
    </section>

    <section>
      <h3 class="text-lg font-semibold mb-2">パスワード変更</h3>
      <UpdatePasswordForm />
    </section>

    <section>
      <h3 class="text-lg font-semibold mb-2 text-red-600">アカウント削除</h3>
      <DeleteUserForm />
    </section>
  </div>
</template>
