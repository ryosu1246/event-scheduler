<template>
  <div>
    <h3>日程への回答</h3>
    <div v-for="schedule in schedules" :key="schedule.id" class="mb-2">
      <span>{{ schedule.date }}</span>
      <button
        class="ml-2 px-2 py-1 bg-green-500 text-white rounded"
        @click="respond(schedule.id, 'yes')"
      >
        参加
      </button>
      <button
        class="ml-2 px-2 py-1 bg-red-500 text-white rounded"
        @click="respond(schedule.id, 'no')"
      >
        不参加
      </button>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    schedules: Array,
  },
  methods: {
    async respond(scheduleId, status) {
      await fetch("/responses", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content"),
        },
        body: JSON.stringify({
          event_schedule_id: scheduleId,
          status: status,
        }),
      });
      alert("回答を保存しました！");
    },
  },
};
</script>
