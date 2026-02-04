<template>
  <div>
    <!-- イベント一覧 -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div
        v-for="event in events"
        :key="event.id"
        class="p-4 border border-stone-200 rounded-lg cursor-pointer hover:bg-stone-50 hover:shadow-sm transition"
        @click="openDetail(event)"
      >
        <h3 class="text-lg font-semibold text-stone-800">{{ event.title }}</h3>
      </div>
    </div>

    <!-- イベントがない場合 -->
    <div v-if="events.length === 0 && !isLoading" class="text-center text-stone-500 py-8">
      まだイベントがありません。
    </div>

    <!-- モーダル本体 -->
    <div
      v-if="selectedEvent"
      class="fixed inset-0 bg-black/50 z-50"
      @click.self="closeModal"
    ></div>
    <div
      v-if="selectedEvent"
      class="fixed top-[4rem] left-0 right-0 bottom-0 z-50 bg-white rounded-t-2xl shadow-xl flex flex-col overflow-hidden md:left-1/2 md:-translate-x-1/2 md:max-w-2xl md:rounded-2xl md:bottom-8">

        <!-- モーダルヘッダー -->
        <div class="sticky top-0 bg-white border-b border-stone-200 px-4 py-3 flex items-center justify-between shrink-0">
          <h2 class="text-lg font-bold text-stone-800">
            <span v-if="modalPage === 'detail'">{{ selectedEvent.title }}</span>
            <span v-else-if="modalPage === 'response'">出欠回答</span>
            <span v-else-if="modalPage === 'respondents'">回答状況</span>
            <span v-else-if="modalPage === 'feeCalculator'">会費計算</span>
          </h2>
          <button
            class="p-1 text-stone-400 hover:text-stone-600 rounded-full hover:bg-stone-100 transition"
            @click="closeModal"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- モーダルコンテンツ -->
        <div class="flex-1 overflow-y-auto p-4">

          <!-- ページ1: 詳細 -->
          <div v-if="modalPage === 'detail'">
            <p class="mb-4 text-stone-600">{{ selectedEvent.description }}</p>

            <div v-if="selectedEvent.venue?.name" class="mt-4">
              <h3 class="text-stone-500 text-sm font-medium">会場</h3>
              <p class="text-stone-600 mt-1">{{ selectedEvent.venue.name }}</p>

              <a
                  v-if="selectedEvent.venue.latitude && selectedEvent.venue.longitude"
                  :href="`https://www.google.com/maps?q=${selectedEvent.venue.latitude},${selectedEvent.venue.longitude}`"
                  target="_blank"
                  rel="noopener"
                  class="inline-block mt-2 px-3 py-1 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm transition"
              >
                  マップで開く
              </a>
            </div>

            <ul class="mt-4 text-stone-700 divide-y divide-stone-100">
              <li
                  v-for="schedule in selectedEvent.schedules"
                  :key="schedule.id"
                  class="flex justify-between items-center py-2 px-3 hover:bg-stone-50 rounded-lg cursor-pointer transition"
                  @click="showRespondents(schedule)"
              >
                  <span>{{ formatDateWithDay(schedule.date) }}</span>
                  <span class="text-sm text-stone-500 inline-flex items-center gap-3">
                    <span>⚪︎{{ schedule.response_summary?.yes || 0 }}</span>
                    <span>△{{ schedule.response_summary?.maybe || 0 }}</span>
                    <span>×{{ schedule.response_summary?.no || 0 }}</span>
                  </span>
              </li>
            </ul>

            <div class="mt-6 text-center">
            <button
              class="w-full px-4 py-2.5 bg-amber-800 text-white rounded-lg hover:bg-amber-900 font-medium transition shadow-sm"
              @click="goToResponse(selectedEvent.id)"
            >
              {{ selectedEvent.is_responded ? '修正する' : '回答する' }}
            </button>
            </div>

            <!-- 編集・削除ボタン（イベント作成者のみ表示） -->
            <div v-if="selectedEvent.created_by === currentUserId" class="flex flex-col gap-2 mt-4">
              <button
                @click="openEditModal(selectedEvent)"
                class="w-full px-4 py-2.5 bg-stone-600 text-white rounded-lg hover:bg-stone-700 font-medium transition shadow-sm"
              >
                編集
              </button>
              <button
                @click="confirmDelete(selectedEvent)"
                class="w-full px-4 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition shadow-sm"
              >
                削除
              </button>
            </div>
          </div>

          <!-- 編集モーダル -->
          <div
          v-if="isEditModalOpen"
          class="fixed inset-0 bg-black/50 z-50"
          @click.self="closeEditModal"
          ></div>
          <div
          v-if="isEditModalOpen"
          class="fixed top-[4rem] left-0 right-0 bottom-0 z-50 bg-white rounded-t-2xl shadow-xl flex flex-col overflow-hidden md:left-1/2 md:-translate-x-1/2 md:max-w-2xl md:rounded-2xl md:bottom-8">
              <div class="sticky top-0 bg-white border-b border-stone-200 px-4 py-3 flex items-center justify-between shrink-0">
                <h2 class="text-lg font-bold text-stone-800">イベントを編集</h2>
                <button class="p-1 text-stone-400 hover:text-stone-600 rounded-full hover:bg-stone-100 transition" @click="closeEditModal">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
              <div class="flex-1 overflow-y-auto p-4">
                <label class="block mb-4">
                  <span class="block text-sm font-medium text-stone-700 mb-1">タイトル</span>
                  <input v-model="editForm.title" class="border border-stone-300 rounded-md w-full p-2 focus:border-amber-500 focus:ring-amber-500" />
                </label>

                <label class="block mb-4">
                  <span class="block text-sm font-medium text-stone-700 mb-1">説明</span>
                  <textarea v-model="editForm.description" class="border border-stone-300 rounded-md w-full p-2 focus:border-amber-500 focus:ring-amber-500"></textarea>
                </label>

                <div class="mb-4">
                  <span class="block text-sm font-medium text-stone-700 mb-1">日付</span>
                  <div v-for="(date, index) in editForm.dates" :key="index" class="flex items-center gap-2 mb-2">
                    <input type="date" v-model="editForm.dates[index]" class="border border-stone-300 p-2 rounded-md flex-1 focus:border-amber-500 focus:ring-amber-500"/>
                    <button @click="removeDate(index)" class="text-red-600 text-sm hover:text-red-700">削除</button>
                  </div>
                  <button @click="addDate" class="text-amber-800 hover:underline text-sm">
                  ＋ 日程を追加
                  </button>
                </div>

                <div class="mb-4">
                  <EventVenue
                  :initialVenue="editForm.venue"
                  @updateVenue="(venue) => editForm.venue = venue"
                  />
                </div>

                <div class="flex justify-end mt-4 gap-2">
                  <button class="px-4 py-2 bg-stone-200 text-stone-700 rounded-md hover:bg-stone-300 transition" @click="closeEditModal">キャンセル</button>
                  <button class="px-4 py-2 bg-amber-800 text-white rounded-md hover:bg-amber-900 transition shadow-sm" @click="updateEvent">更新</button>
                </div>
              </div>
          </div>

          <!-- ページ2: 回答 -->
          <div v-else-if="modalPage === 'response'">
            <div
              v-for="schedule in selectedEvent.schedules"
              :key="schedule.id"
              class="mb-4"
            >
              <div class="font-medium mb-2 text-stone-700">{{ schedule.date }}</div>

              <div class="flex gap-2">
                <button
                  v-for="option in options"
                  :key="option.value"
                  class="px-4 py-2 rounded-md border transition"
                  :class="{
                  'bg-amber-800 text-white border-amber-800':
                      responses[schedule.id] === option.value,
                  'bg-white text-stone-700 border-stone-300 hover:bg-stone-50':
                      responses[schedule.id] !== option.value
                  }"
                  @click="selectResponse(schedule.id, option.value)"
                >
                  {{ option.label }}
                </button>
              </div>
            </div>

            <div class="mt-6 flex justify-between">
              <button
                type="button"
                class="px-4 py-2 bg-stone-200 text-stone-700 rounded-md hover:bg-stone-300 transition"
                @click="backToDetail"
              >
                戻る
              </button>
              <button
                type="button"
                class="px-4 py-2 rounded-md font-semibold transition shadow-sm"
                :class="isAllAnswered ? 'bg-green-600 text-white hover:bg-green-700' : 'bg-stone-200 text-stone-400 cursor-not-allowed'"
                :disabled="!isAllAnswered"
                @click="submitResponse"
              >
                送信
              </button>
            </div>
          </div>

          <!-- ページ3: 回答者一覧 -->
          <div v-else-if="modalPage === 'respondents'">
            <!-- サブヘッダー -->
            <div class="flex items-center mb-4">
              <button @click="modalPage = 'detail'" class="text-stone-500 hover:text-stone-700 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
              </button>
              <div class="flex-1 text-center">
                <div class="text-sm text-stone-500">{{ formatDateWithDay(selectedSchedule?.date) }}</div>
              </div>
              <!-- 会費計算ボタン -->
              <button @click="openFeeCalculator" class="text-stone-500 hover:text-stone-700 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
              </button>
            </div>

            <!-- 回答者リスト -->
            <ul class="divide-y divide-stone-200">
              <li v-for="respondent in scheduleRespondents" :key="respondent.user_id"
                  class="flex items-center py-3">
                <img :src="respondent.profile_image || '/images/default-avatar.png'"
                     class="w-10 h-10 rounded-full object-cover mr-3"
                     alt="">
                <span class="flex-1 text-stone-700">{{ respondent.name }}</span>
                <span class="text-xl">
                  {{ respondent.response === 'yes' ? '○' : respondent.response === 'maybe' ? '△' : '×' }}
                </span>
              </li>
            </ul>

            <p v-if="scheduleRespondents.length === 0" class="text-center text-stone-500 py-4">
              まだ回答がありません
            </p>
          </div>

          <!-- ページ4: 会費計算 -->
          <div v-else-if="modalPage === 'feeCalculator'">
            <!-- サブヘッダー -->
            <div class="flex items-center mb-6">
              <button @click="modalPage = 'respondents'" class="text-stone-500 hover:text-stone-700 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
              </button>
              <div class="flex-1 text-center"></div>
              <div class="w-5"></div>
            </div>

            <!-- 会費合計入力 -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-stone-700 mb-2">会費合計</label>
              <div class="flex items-center">
                <input
                  type="number"
                  v-model="totalFee"
                  class="border border-stone-300 rounded-lg px-4 py-2 w-full text-lg focus:border-amber-500 focus:ring-amber-500"
                  placeholder="0"
                />
                <span class="ml-2 text-stone-600">円</span>
              </div>
            </div>

            <!-- 参加人数 -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-stone-700 mb-2">参加人数</label>
              <div class="flex items-center justify-center gap-4">
                <button
                  @click="decrementParticipants"
                  class="w-10 h-10 rounded-full bg-stone-200 hover:bg-stone-300 text-xl font-bold transition"
                  :disabled="participantCount <= 1"
                >
                  −
                </button>
                <span class="text-2xl font-semibold w-16 text-center text-stone-800">{{ participantCount }}</span>
                <button
                  @click="incrementParticipants"
                  class="w-10 h-10 rounded-full bg-stone-200 hover:bg-stone-300 text-xl font-bold transition"
                >
                  ＋
                </button>
              </div>
            </div>

            <!-- 計算ボタン -->
            <div class="text-center mb-6">
            <button
              @click="calculateFee"
              class="py-3 px-8 bg-amber-800 text-white rounded-lg font-semibold hover:bg-amber-900 transition shadow-sm"
              :disabled="!totalFee || participantCount < 1"
            >
              ＝
            </button>
            </div>

            <!-- 計算結果 -->
            <div v-if="calculatedFee !== null" class="text-center p-4 bg-stone-50 rounded-lg">
              <div class="text-sm text-stone-500 mb-1">一人あたり</div>
              <div class="text-3xl font-bold text-amber-800">{{ calculatedFee.toLocaleString() }} 円</div>
            </div>
          </div>

        </div>
      </div>

    <!-- グループ管理モーダル -->
    <div
      v-if="showManageModal"
      class="fixed inset-0 bg-black/50 z-50"
      @click.self="closeManageModal"
    ></div>
    <div
      v-if="showManageModal"
      class="fixed top-[4rem] left-0 right-0 bottom-0 z-50 bg-white rounded-t-2xl shadow-xl flex flex-col overflow-hidden md:left-1/2 md:-translate-x-1/2 md:max-w-2xl md:rounded-2xl md:bottom-8">
        <!-- モーダルヘッダー -->
        <div class="sticky top-0 bg-white border-b border-stone-200 px-4 py-3 flex items-center justify-between shrink-0">
          <h2 class="text-lg font-bold text-stone-800">{{ groupInfo.name }}</h2>
          <button
            class="p-1 text-stone-400 hover:text-stone-600 rounded-full hover:bg-stone-100 transition"
            @click="closeManageModal"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- タブ -->
        <div class="flex border-b border-stone-200 shrink-0">
          <button
            v-if="groupInfo.isAdmin"
            :class="['px-4 py-2.5 text-sm font-medium transition', manageTab === 'edit' ? 'border-b-2 border-amber-800 text-amber-800' : 'text-stone-500 hover:text-stone-700']"
            @click="manageTab = 'edit'"
          >
            編集
          </button>
          <button
            :class="['px-4 py-2.5 text-sm font-medium transition', manageTab === 'members' ? 'border-b-2 border-amber-800 text-amber-800' : 'text-stone-500 hover:text-stone-700']"
            @click="manageTab = 'members'"
          >
            メンバー
          </button>
          <button
            :class="['px-4 py-2.5 text-sm font-medium transition', manageTab === 'invite' ? 'border-b-2 border-amber-800 text-amber-800' : 'text-stone-500 hover:text-stone-700']"
            @click="manageTab = 'invite'"
          >
            招待
          </button>
          <button
            :class="['px-4 py-2.5 text-sm font-medium transition', manageTab === 'leave' ? 'border-b-2 border-red-600 text-red-600' : 'text-stone-500 hover:text-stone-700']"
            @click="manageTab = 'leave'"
          >
            退会
          </button>
        </div>

        <!-- タブコンテンツ -->
        <div class="flex-1 overflow-y-auto p-4">

          <!-- 編集タブ -->
          <div v-if="manageTab === 'edit'">
            <form @submit.prevent="updateGroup">
              <div class="mb-4">
                <label class="block text-sm font-medium text-stone-700 mb-1">グループ名 *</label>
                <input
                  v-model="groupEditForm.name"
                  type="text"
                  required
                  class="border border-stone-300 rounded-md w-full p-2 focus:border-amber-500 focus:ring-amber-500"
                />
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium text-stone-700 mb-1">説明</label>
                <textarea
                  v-model="groupEditForm.description"
                  class="border border-stone-300 rounded-md w-full p-2 focus:border-amber-500 focus:ring-amber-500"
                  rows="3"
                ></textarea>
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium text-stone-700 mb-1">グループ画像</label>
                <input
                  type="file"
                  accept="image/*"
                  @change="handleGroupIconChange"
                  class="border border-stone-300 rounded-md w-full p-2 text-sm"
                />
              </div>
              <div class="flex flex-col gap-2">
                <button
                  type="submit"
                  class="w-full px-4 py-2.5 bg-amber-800 text-white rounded-lg hover:bg-amber-900 font-medium transition shadow-sm"
                >
                  更新
                </button>
                <button
                  type="button"
                  @click="confirmDeleteGroup"
                  class="w-full px-4 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition shadow-sm"
                >
                  グループを削除
                </button>
              </div>
            </form>
          </div>

          <!-- メンバータブ -->
          <div v-if="manageTab === 'members'">
            <h3 class="text-sm font-medium text-stone-500 mb-3">メンバー {{ members.length }}人</h3>
            <div v-if="members.length === 0" class="text-stone-500 text-center py-4">
              メンバーがいません
            </div>
            <div
              v-for="member in members"
              :key="member.id"
              class="flex items-center justify-between py-3 border-b border-stone-100"
            >
              <div class="flex items-center">
                <img :src="member.profile_image_url" alt="" class="w-8 h-8 rounded-full mr-3" />
                <p class="font-medium text-stone-700">{{ member.name }}</p>
                <span
                  v-if="member.role === 'admin'"
                  class="ml-2 px-2 py-0.5 text-xs bg-amber-100 text-amber-800 rounded-full"
                >
                  管理者
                </span>
              </div>
              <button
                v-if="groupInfo.isAdmin && member.id !== currentUserId"
                @click="removeMember(member.id)"
                class="px-3 py-1 text-sm bg-red-50 text-red-600 rounded-md hover:bg-red-100 transition"
              >
                削除
              </button>
            </div>
          </div>

          <!-- 招待タブ -->
          <div v-if="manageTab === 'invite'">
            <div class="mb-6 text-center">
              <button
                @click="generateInviteLink"
                class="px-4 py-2.5 bg-amber-800 text-white rounded-md hover:bg-amber-900 font-medium transition shadow-sm"
                :disabled="isGeneratingInvite"
              >
                {{ isGeneratingInvite ? '生成中...' : '招待リンクを生成' }}
              </button>
            </div>

            <div v-if="inviteData" class="space-y-4">
              <!-- QRコード -->
              <div class="text-center">
                <p class="text-sm text-stone-500 mb-2">QRコード</p>
                <div ref="qrcodeContainer" class="flex justify-center"></div>
              </div>

              <!-- 招待リンク -->
              <div>
                <p class="text-sm text-stone-500 mb-2">招待リンク</p>
                <div class="flex gap-2">
                  <input
                    type="text"
                    :value="inviteData.invite_url"
                    readonly
                    class="border border-stone-300 rounded-md p-2 flex-1 bg-stone-50 text-sm"
                  />
                  <button
                    @click="copyInviteLink"
                    class="px-3 py-2 bg-stone-200 text-stone-700 rounded-md hover:bg-stone-300 text-sm transition"
                  >
                    {{ copiedLink ? 'コピー済' : 'コピー' }}
                  </button>
                </div>
              </div>

              <!-- 有効期限 -->
              <p class="text-xs text-stone-500 text-center">
                有効期限: {{ formatExpiresAt(inviteData.expires_at) }}
              </p>
            </div>
          </div>

          <!-- 退会タブ -->
          <div v-if="manageTab === 'leave'">
            <div class="text-center py-6">
              <div class="mb-4">
                <svg class="w-16 h-16 mx-auto text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
              </div>
              <h3 class="text-lg font-semibold text-stone-800 mb-2">グループを退会しますか？</h3>
              <p class="text-stone-600 mb-6">
                このグループから退会すると、グループ内のイベントにアクセスできなくなります。
              </p>
              <button
                @click="confirmLeaveGroup"
                class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition"
                :disabled="isLeaving"
              >
                {{ isLeaving ? '処理中...' : 'グループを退会する' }}
              </button>
            </div>
          </div>

        </div>
      </div>

  </div>
</template>

<script>
import axios from 'axios';
import EventVenue from './EventVenue.vue';
import QRCode from 'qrcode';

export default {
  components: { EventVenue },
  data() {
    return {
      currentUserId: window.currentUserId,
      groupId: window.groupId || null,
      groupInfo: window.groupInfo || {},
      events: [],
      selectedEvent: null,
      modalPage: 'detail',
      responses: {},
      isLoading: false,

      // ⚪︎△×オプション設定
      options: [
        { label: '⚪︎', value: 'yes' },
        { label: '△', value: 'maybe' },
        { label: '×', value: 'no' },
      ],

      editForm: { id: null, title: '', description: '', date: [], venue: { name: '', lat: null, lng: null }, },
      isEditModalOpen: false,

      // 回答者一覧
      selectedSchedule: null,
      scheduleRespondents: [],

      // 会費計算用
      totalFee: '',
      participantCount: 0,
      calculatedFee: null,

      // グループ管理モーダル
      showManageModal: false,
      manageTab: 'members',
      groupEditForm: {
        name: '',
        description: '',
        icon: null,
      },
      members: [],

      // 招待機能
      inviteData: null,
      isGeneratingInvite: false,
      copiedLink: false,

      // 退会機能
      isLeaving: false,
    };
  },
  computed: {
    // すべてのスケジュールに回答があるかチェック
    isAllAnswered() {
      if (!this.selectedEvent?.schedules?.length) return false;
      return this.selectedEvent.schedules.every(
        s => this.responses[s.id] && this.responses[s.id] !== ''
      );
    },
  },
  methods: {
    openDetail(event) {
      this.selectedEvent = event;
      this.modalPage = 'detail';
      this.responses = {};
    },
    goToResponse() {
      this.modalPage = 'response';
    },
    backToDetail() {
      this.modalPage = 'detail';
    },
    closeModal() {
      this.selectedEvent = null;
      this.responses = {};
      this.selectedSchedule = null;
      this.scheduleRespondents = [];
    },
    formatDateWithDay(dateStr) {
      if (!dateStr) return '';
      const date = new Date(dateStr);
      const days = ['日', '月', '火', '水', '木', '金', '土'];
      const year = date.getFullYear();
      const month = date.getMonth() + 1;
      const day = date.getDate();
      const dayOfWeek = days[date.getDay()];
      return `${year}/${month}/${day}（${dayOfWeek}）`;
    },
    async showRespondents(schedule) {
      this.selectedSchedule = schedule;
      this.modalPage = 'respondents';

      try {
        const res = await axios.get(`/schedules/${schedule.id}/respondents`);
        this.scheduleRespondents = res.data;
      } catch (error) {
        console.error('回答者取得エラー:', error);
        this.scheduleRespondents = [];
      }
    },
    openFeeCalculator() {
      const yesCount = this.scheduleRespondents.filter(r => r.response === 'yes').length;
      this.participantCount = yesCount || 1;
      this.totalFee = '';
      this.calculatedFee = null;
      this.modalPage = 'feeCalculator';
    },
    incrementParticipants() {
      this.participantCount++;
    },
    decrementParticipants() {
      if (this.participantCount > 1) {
        this.participantCount--;
      }
    },
    calculateFee() {
      if (this.totalFee && this.participantCount > 0) {
        this.calculatedFee = Math.ceil(Number(this.totalFee) / this.participantCount);
      }
    },
    selectResponse(scheduleId, value) {
      this.responses = { ...this.responses, [scheduleId]: value };
    },
    async submitResponse() {
      try {
        await axios.post(`/events/${this.selectedEvent.id}/responses`, {
          responses: this.responses,
        });

        await this.fetchSummary(this.selectedEvent.id);

        alert('回答を送信しました！');
        this.modalPage = 'detail';
      } catch (error) {
        console.error(error);
        alert('送信中にエラーが発生しました');
      }
    },

    async fetchEvents() {
      this.isLoading = true;
      try {
        let url = '/events/json';
        if (this.groupId) {
          url += `?group_id=${this.groupId}`;
        }
        const response = await axios.get(url);
        this.events = response.data;

        for (const event of this.events) {
            this.fetchSummary(event.id);
        }
      } catch (error) {
        console.error('イベント一覧取得エラー', error);
      } finally {
        this.isLoading = false;
      }
    },

    async fetchSummary(eventId) {
      try {
        const res = await axios.get(`/events/${eventId}/summary`);
        const summaryList = res.data;

        const event = this.events.find(e => e.id === eventId);
        if (!event) return;

        for (const schedule of event.schedules) {
          const match = summaryList.find(s => s.event_schedule_id === schedule.id);
          schedule.response_summary = match || { yes: 0, maybe: 0, no: 0 };
        }

        if (this.selectedEvent && this.selectedEvent.id === eventId) {
          this.selectedEvent = { ...event };
        }

      } catch (error) {
        console.error('集計取得エラー', error);
      }
    },

    openEditModal(event) {
        this.editForm = {
            id: event.id,
            title: event.title,
            description: event.description,
            dates: event.schedules?.map(s => s.date) || [],
            venue: {
                name: event.venue?.name || '',
                lat: event.venue?.lat || event.venue?.latitude || '',
                lng: event.venue?.lng || event.venue?.longitude || ''
            }
        };
        this.isEditModalOpen = true;
    },

    closeEditModal() {
        this.isEditModalOpen = false;
    },

    addDate() {
        this.editForm.dates.push('');
    },

    removeDate(index) {
        this.editForm.dates.splice(index, 1);
    },

    async updateEvent() {
        const payload = {
            ...this.editForm,
            dates: this.editForm.dates ?? []
        }

        try {
            await axios.put(`/events/${this.editForm.id}`, payload)
            this.isEditModalOpen = false
            await this.fetchEvents()
        } catch (error) {
            console.error("バリデーションエラー内容:", error.response?.data)
        }
    },

    async confirmDelete(event) {
        if (!confirm('本当に削除しますか？')) return;
        try {
            await axios.delete(`/events/${event.id}`);
            alert('イベントを削除しました');
            this.closeModal();
            this.fetchEvents();
        } catch (error) {
            console.error(error);
            alert('削除に失敗しました');
        }
    },

    // グループ管理モーダル
    openManageModal() {
      this.groupEditForm = {
        name: this.groupInfo.name,
        description: this.groupInfo.description || '',
        icon: null,
      };
      this.manageTab = 'members';
      this.inviteData = null;
      this.showManageModal = true;
      this.fetchMembers();
    },

    closeManageModal() {
      this.showManageModal = false;
    },

    handleGroupIconChange(event) {
      this.groupEditForm.icon = event.target.files[0];
    },

    async updateGroup() {
      try {
        const formData = new FormData();
        formData.append('name', this.groupEditForm.name);
        formData.append('description', this.groupEditForm.description || '');
        formData.append('_method', 'PUT');
        if (this.groupEditForm.icon) {
          formData.append('icon', this.groupEditForm.icon);
        }

        const response = await axios.post(`/groups/${this.groupId}`, formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        });

        this.groupInfo.name = response.data.group.name;
        this.groupInfo.description = response.data.group.description;
        this.groupInfo.iconUrl = response.data.group.icon_url;

        alert('グループを更新しました');
      } catch (error) {
        console.error('グループ更新エラー', error);
        alert('グループの更新に失敗しました');
      }
    },

    async confirmDeleteGroup() {
      if (!confirm('本当にこのグループを削除しますか？関連するイベントもすべて削除されます。')) {
        return;
      }
      try {
        await axios.delete(`/groups/${this.groupId}`);
        alert('グループを削除しました');
        window.location.href = '/groups';
      } catch (error) {
        console.error('グループ削除エラー', error);
        alert('グループの削除に失敗しました');
      }
    },

    async fetchMembers() {
      try {
        const response = await axios.get(`/groups/${this.groupId}/members`);
        this.members = response.data.members;
      } catch (error) {
        console.error('メンバー取得エラー', error);
      }
    },

    async removeMember(userId) {
      if (!confirm('このメンバーを削除しますか？')) {
        return;
      }
      try {
        await axios.delete(`/groups/${this.groupId}/members`, {
          data: { user_id: userId },
        });
        this.members = this.members.filter(m => m.id !== userId);
        alert('メンバーを削除しました');
      } catch (error) {
        console.error('メンバー削除エラー', error);
        alert(error.response?.data?.message || 'メンバーの削除に失敗しました');
      }
    },

    // 招待リンク生成
    async generateInviteLink() {
      this.isGeneratingInvite = true;
      try {
        const response = await axios.post(`/groups/${this.groupId}/generate-invite`);
        this.inviteData = response.data;
        this.copiedLink = false;

        // QRコード生成
        this.$nextTick(() => {
          this.generateQRCode();
        });
      } catch (error) {
        console.error('招待リンク生成エラー', error);
        alert('招待リンクの生成に失敗しました');
      } finally {
        this.isGeneratingInvite = false;
      }
    },

    generateQRCode() {
      if (!this.inviteData || !this.$refs.qrcodeContainer) return;

      // 既存のQRコードをクリア
      this.$refs.qrcodeContainer.innerHTML = '';

      QRCode.toCanvas(this.inviteData.invite_url, {
        width: 200,
        margin: 2,
      }, (error, canvas) => {
        if (error) {
          console.error('QRコード生成エラー', error);
          return;
        }
        this.$refs.qrcodeContainer.appendChild(canvas);
      });
    },

    copyInviteLink() {
      if (!this.inviteData) return;

      navigator.clipboard.writeText(this.inviteData.invite_url).then(() => {
        this.copiedLink = true;
        setTimeout(() => {
          this.copiedLink = false;
        }, 2000);
      }).catch(err => {
        console.error('コピー失敗', err);
        alert('コピーに失敗しました');
      });
    },

    formatExpiresAt(dateString) {
      const date = new Date(dateString);
      return date.toLocaleString('ja-JP', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
      });
    },

    // グループ退会確認
    async confirmLeaveGroup() {
      const confirmed = confirm(
        'このグループから退会しますか？\n\n' +
        '退会すると、このグループのイベントにアクセスできなくなります。'
      );

      if (!confirmed) return;

      this.isLeaving = true;

      try {
        const response = await axios.post(`/groups/${this.groupId}/leave`);
        alert(response.data.message);
        window.location.href = '/groups';
      } catch (error) {
        console.error('グループ退会エラー', error);
        alert(error.response?.data?.message || 'グループの退会に失敗しました');
      } finally {
        this.isLeaving = false;
      }
    },
  },

  created() {
    this.fetchEvents();
  },

  mounted() {
    // 「グループ管理」ボタンのクリックイベントをリッスン
    const manageButton = document.getElementById('open-manage-modal');
    if (manageButton) {
      manageButton.addEventListener('click', () => {
        this.openManageModal();
      });
    }

    // ヘッダーのグループ名クリックイベントをリッスン
    const titleButton = document.getElementById('open-member-modal-title');
    if (titleButton) {
      titleButton.addEventListener('click', () => {
        this.openManageModal();
      });
    }
  },
};
</script>


<style scoped>
button {
  transition: background-color 0.2s ease, color 0.2s ease;
}
</style>
