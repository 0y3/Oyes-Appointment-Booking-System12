<script setup>
import { ref } from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

const props = defineProps({
    bookings: Array
});

const calendarOptions = ref({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialView: 'timeGridWeek',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
    events: props.bookings.map(booking => ({
        title: `${booking.visitor_name} (${booking.visitor_email})`,
        start: booking.start_time,
        end: booking.end_time,
        extendedProps: {
            description: booking.description
        }
    })),
    eventClick: (info) => {
        alert(`Booking Details:
        Name: ${info.event.title}
        Start: ${info.event.start.toLocaleString()}
        End: ${info.event.end.toLocaleString()}
        Notes: ${info.event.extendedProps.description || 'None'}`);
    }
});
</script>

<template>
    <h1 class="text-2xl font-bold mb-6">Bookings Calendar</h1>
    <FullCalendar :options="calendarOptions" class="w-full text-gray-900" />
</template>


<!--

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import axios from 'axios';

const bookings = ref([]);

const calendarOptions = ref({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialView: 'timeGridWeek',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
    events: [],
    eventClick: handleEventClick
});

async function fetchBookings() {
    try {
        const response = await axios.get('/api/bookings');
        calendarOptions.value.events = response.data.map(booking => ({
        id: booking.id,
        title: `${booking.visitor_name} (${booking.visitor_email})`,
        start: booking.start_time,
        end: booking.end_time,
        extendedProps: {
            description: booking.description
        }
        }));
    } catch (error) {
        console.error('Error fetching bookings:', error);
    }
}

function handleEventClick(info) {
    alert(`Booking Details:
    Name: ${info.event.title}
    Start: ${info.event.start.toLocaleString()}
    End: ${info.event.end.toLocaleString()}
    Notes: ${info.event.extendedProps.description || 'None'}`);
}

onMounted(() => {
    fetchBookings();
});
</script> -->
