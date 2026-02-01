<template>
  <div>
    <!-- トークン招待モーダル -->
    <div
      v-if="pendingInvitation"
      class="fixed inset-0 bg-black/50 z-50"
      @click.self="declineTokenInvitation"
    ></div>
    <div
      v-if="pendingInvitation"
      class="fixed top-[4rem] left-0 right-0 bottom-0 z-50 bg-white rounded-t-2xl shadow-xl flex flex-col overflow-hidden"
    >
        <!-- モーダルヘッダー -->
        <div class="sticky top-0 bg-white border-b border-stone-200 px-4 py-3 flex items-center justify-between shrink-0">
          <h2 class="text-lg font-bold text-stone-800">グループへの招待</h2>
          <button @click="declineTokenInvitation" class="p-1 text-stone-400 hover:text-stone-600 rounded-full hover:bg-stone-100 transition">✕</button>
        </div>
        <!-- コンテンツ -->
        <div class="flex-1 overflow-y-auto p-6">
          <div class="text-center">
            <p class="text-stone-700 mb-6">
              <span class="font-semibold">{{ pendingInvitation.inviter_name }}</span>
              から
              「<span class="font-semibold">{{ pendingInvitation.group_name }}</span>」
              に招待されました。
            </p>
            <div class="flex justify-center gap-4">
              <button
                @click="acceptTokenInvitation"
                class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 shadow-sm font-medium transition"
              >
                参加する
              </button>
              <button
                @click="declineTokenInvitation"
                class="px-6 py-2 bg-stone-200 text-stone-700 rounded-md hover:bg-stone-300 shadow-sm font-medium transition"
              >
                参加しない
              </button>
            </div>
          </div>
        </div>
    </div>

    <!-- 招待通知バナー（旧方式：メール招待用・残す） -->
    <div v-if="invitations.length > 0" class="mb-6">
      <div
        v-for="invitation in invitations"
        :key="invitation.id"
        class="flex items-center justify-between p-4 bg-amber-50 border border-amber-200 rounded-lg mb-2"
      >
        <div class="flex items-center">
          <img :src="invitation.icon_url" alt="" class="w-10 h-10 rounded-full mr-3" />
          <div>
            <p class="font-semibold text-stone-800">{{ invitation.name }} への招待があります</p>
            <p class="text-sm text-stone-500">{{ invitation.description }}</p>
          </div>
        </div>
        <div class="flex gap-2">
          <button
            @click="acceptInvitation(invitation.id)"
            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 shadow-sm font-medium transition"
          >
            承認
          </button>
          <button
            @click="declineInvitation(invitation.id)"
            class="px-4 py-2 bg-stone-200 text-stone-700 rounded-md hover:bg-stone-300 shadow-sm font-medium transition"
          >
            拒否
          </button>
        </div>
      </div>
    </div>

    <!-- グループ作成ボタン -->
    <div class="mb-6">
      <button
        @click="openCreateModal"
        class="px-4 py-2 bg-amber-800 text-white rounded-md hover:bg-amber-900 shadow-sm font-medium transition"
      >
        + グループを作成
      </button>
    </div>

    <!-- グループ一覧 -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div
        v-for="group in groups"
        :key="group.id"
        class="cursor-pointer"
        @click="navigateToGroup(group.id)"
      >
        <div class="border border-stone-200 rounded-lg overflow-hidden shadow-sm hover:shadow hover:bg-stone-50 transition">
          <div class="aspect-video bg-stone-100">
            <img :src="group.icon_url" alt="" class="w-full h-full object-cover" />
          </div>
        </div>
        <div class="px-1 pt-2">
          <h3 class="text-sm font-semibold text-stone-800 truncate">{{ group.name }}</h3>
          <p class="text-xs text-stone-500 text-right">メンバー　{{ group.member_count }}人</p>
        </div>
      </div>
    </div>

    <!-- グループがない場合 -->
    <div v-if="groups.length === 0 && !isLoading" class="text-center text-stone-500 py-8">
      まだグループがありません。新しいグループを作成してください。
    </div>

    <!-- モーダル：グループ作成 -->
    <div
      v-if="showCreateModal"
      class="fixed inset-0 bg-black/50 z-50"
      @click.self="closeCreateModal"
    ></div>
    <div
      v-if="showCreateModal"
      class="fixed top-[4rem] left-0 right-0 bottom-0 z-50 bg-white rounded-t-2xl shadow-xl flex flex-col overflow-hidden"
    >
        <!-- モーダルヘッダー -->
        <div class="sticky top-0 bg-white border-b border-stone-200 px-4 py-3 flex items-center justify-between shrink-0">
          <h2 class="text-lg font-bold text-stone-800">グループを作成</h2>
          <button
            class="p-1 text-stone-400 hover:text-stone-600 rounded-full hover:bg-stone-100 transition"
            @click="closeCreateModal"
          >
            ✕
          </button>
        </div>
        <!-- コンテンツ -->
        <div class="flex-1 overflow-y-auto p-4">
          <form @submit.prevent="createGroup">
            <div class="mb-4">
              <label class="block font-medium mb-1 text-stone-700">グループ名 *</label>
              <input
                v-model="createForm.name"
                type="text"
                required
                class="border border-stone-300 rounded-md w-full p-2 focus:border-amber-500 focus:ring-amber-500"
              />
            </div>
            <div class="mb-4">
              <label class="block font-medium mb-1 text-stone-700">説明</label>
              <textarea
                v-model="createForm.description"
                class="border border-stone-300 rounded-md w-full p-2 focus:border-amber-500 focus:ring-amber-500"
                rows="3"
              ></textarea>
            </div>
            <div class="mb-4">
              <label class="block font-medium mb-1 text-stone-700">アイコン画像</label>
              <input
                type="file"
                accept="image/*"
                @change="handleIconChange"
                class="border border-stone-300 rounded-md w-full p-2"
              />
            </div>
            <div class="flex justify-end gap-2">
              <button
                type="button"
                @click="closeCreateModal"
                class="px-4 py-2 bg-stone-200 text-stone-700 rounded-md hover:bg-stone-300 shadow-sm font-medium transition"
              >
                キャンセル
              </button>
              <button
                type="submit"
                class="px-4 py-2 bg-amber-800 text-white rounded-md hover:bg-amber-900 shadow-sm font-medium transition"
              >
                作成
              </button>
            </div>
          </form>
        </div>
    </div>

  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      currentUserId: window.currentUserId,
      groups: [],
      invitations: [],
      isLoading: false,

      // 作成モーダル
      showCreateModal: false,
      createForm: {
        name: '',
        description: '',
        icon: null,
      },

      // トークン招待
      pendingInvitation: null,
    };
  },
  methods: {
    async fetchGroups() {
      this.isLoading = true;
      try {
        const response = await axios.get('/groups/json');
        this.groups = response.data.groups;
        this.invitations = response.data.invitations;
      } catch (error) {
        console.error('グループ一覧取得エラー', error);
      } finally {
        this.isLoading = false;
      }
    },

    async checkPendingInvitation() {
      try {
        const response = await axios.get('/groups/pending-invitation');
        if (response.data.invitation) {
          this.pendingInvitation = response.data.invitation;
        }
      } catch (error) {
        console.error('招待情報取得エラー', error);
      }
    },

    navigateToGroup(groupId) {
      window.location.href = `/groups/${groupId}/events`;
    },

    // 作成モーダル
    openCreateModal() {
      this.createForm = { name: '', description: '', icon: null };
      this.showCreateModal = true;
    },
    closeCreateModal() {
      this.showCreateModal = false;
    },
    handleIconChange(event) {
      this.createForm.icon = event.target.files[0];
    },
    async createGroup() {
      try {
        const formData = new FormData();
        formData.append('name', this.createForm.name);
        formData.append('description', this.createForm.description || '');
        if (this.createForm.icon) {
          formData.append('icon', this.createForm.icon);
        }

        const response = await axios.post('/groups', formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        });

        this.groups.push(response.data.group);
        this.closeCreateModal();
        alert('グループを作成しました');
      } catch (error) {
        console.error('グループ作成エラー', error);
        alert('グループの作成に失敗しました');
      }
    },

    // 招待の承認・拒否（旧方式：メール招待用）
    async acceptInvitation(groupId) {
      try {
        await axios.post(`/groups/${groupId}/accept`);
        await this.fetchGroups();
        alert('グループに参加しました');
      } catch (error) {
        console.error('招待承認エラー', error);
        alert('招待の承認に失敗しました');
      }
    },
    async declineInvitation(groupId) {
      try {
        await axios.post(`/groups/${groupId}/decline`);
        this.invitations = this.invitations.filter(i => i.id !== groupId);
        alert('招待を拒否しました');
      } catch (error) {
        console.error('招待拒否エラー', error);
        alert('招待の拒否に失敗しました');
      }
    },

    // トークン招待の承認・拒否
    async acceptTokenInvitation() {
      if (!this.pendingInvitation) return;

      try {
        const response = await axios.post(`/invite/${this.pendingInvitation.token}/join`);
        this.pendingInvitation = null;

        if (response.data.redirect) {
          window.location.href = response.data.redirect;
        } else {
          await this.fetchGroups();
          alert('グループに参加しました');
        }
      } catch (error) {
        console.error('招待承認エラー', error);
        alert(error.response?.data?.message || '招待の承認に失敗しました');
      }
    },

    async declineTokenInvitation() {
      if (!this.pendingInvitation) return;

      try {
        await axios.post(`/invite/${this.pendingInvitation.token}/decline`);
        this.pendingInvitation = null;
      } catch (error) {
        console.error('招待拒否エラー', error);
      }
    },
  },

  created() {
    this.fetchGroups();
    this.checkPendingInvitation();
  },
};
</script>

<style scoped>
button {
  transition: background-color 0.2s ease;
}
</style>
