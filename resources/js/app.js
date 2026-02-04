import './bootstrap';
import { createApp } from 'vue';
import EventSchedule from './components/EventSchedule.vue';
import EventList from './components/EventList.vue';
import EventVenue from './Components/EventVenue.vue';
import GroupList from './components/GroupList.vue';

// イベントスケジュール（日程登録）コンポーネントのマウント
const scheduleEl = document.getElementById('app');
if (scheduleEl) {
    const scheduleApp = createApp({});
    scheduleApp.component('event-schedule', EventSchedule);
    scheduleApp.mount('#app');
}

// イベント一覧コンポーネントのマウント
const listEl = document.getElementById('vue-event-list');
if (listEl) {
    const listApp = createApp(EventList);
    listApp.mount('#vue-event-list');
}

// イベント会場登録コンポーネントのマウント
const venueEl = document.getElementById('vue-event-venue');
if (venueEl) {
    const venueApp = createApp({});
    venueApp.component('event-venue', EventVenue);
    venueApp.mount('#vue-event-venue');
}

// グループ一覧コンポーネントのマウント
const groupListEl = document.getElementById('vue-group-list');
if (groupListEl) {
    const groupListApp = createApp(GroupList);
    groupListApp.mount('#vue-group-list');
}

