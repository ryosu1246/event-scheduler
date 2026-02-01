<template>
  <div>
    <h1 class="text-sm font-medium text-stone-800 mb-2">スケジュール</h1>

    <div class="flex items-center gap-2">
      <input type="date" v-model="newDate" class="w-64 border border-stone-300 rounded-md p-2 focus:border-amber-500 focus:ring-amber-500" />
      <button type="button" @click="addDate" class="px-8 py-2 bg-amber-800 text-white rounded-md hover:bg-amber-900 shadow-sm font-medium transition">追加</button>
    </div>

    <ul class="mt-3 space-y-1">
      <!-- key を一意な id に変更 -->
      <li v-for="(item, index) in dates" :key="item.id" class="flex items-center gap-2 text-stone-700">
        {{ item.date }}
        <button type="button" @click="removeDate(index)" class="px-2 py-1 text-sm bg-red-600 text-white rounded-md hover:bg-red-700 transition">削除</button>
      </li>
    </ul>

    <!-- hidden input は日付のみ送信 -->
    <input type="hidden" name="dates" :value="JSON.stringify(dates.map(d => d.date))">
  </div>
</template>

<script>
export default {
  name: "EventSchedule",
  data() {
    return {
      newDate: "",
      dates: [],
    };
  },
  methods: {
    addDate() {
      if (this.newDate && !this.dates.some(d => d.date === this.newDate)) {
        this.dates.push({
          id: Date.now(), // 一意なID
          date: this.newDate,
        });
        this.dates.sort((a, b) => a.date.localeCompare(b.date));
        this.newDate = "";
      }
    },
    removeDate(index) {
      this.dates.splice(index, 1);
    },
  },
};
</script>
