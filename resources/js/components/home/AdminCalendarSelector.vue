<script setup>
const props = defineProps({
    calendars: Array
});

const submit = () => {
    Inertia.post('/calendar/select', {
        calendar_id: selectedCalendar.value,
        timezone: selectedTimezone.value
    });
};
</script>

<template>
    <form @submit.prevent="submit">
        <select v-model="selectedCalendar" class="w-full p-3 border rounded mb-4">
            <option v-for="calendar in calendars" :value="calendar.id">
                {{ calendar.summary }} ({{ calendar.timeZone }})
            </option>
        </select>

        <button type="submit" class="bg-green-500 text-white px-6 py-3 rounded-lg">
            Save Calendar Preferences
        </button>
    </form>
</template>
