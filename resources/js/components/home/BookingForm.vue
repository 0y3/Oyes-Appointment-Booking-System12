<template>
    <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold mb-6 text-center">Book an Appointment</h2>

        <div class="mb-4">
            <label class="block text-gray-700 mb-2" for="date">Select Date</label>
            <input
                type="date"
                v-model="selectedDate"
                @change="fetchAvailableSlots"
                class="w-full px-3 py-2 border rounded-md"
                min=""
            >
        </div>

        <div v-if="loading" class="text-center py-4">
        Loading available slots...
        </div>

        <div v-if="availableSlots.length > 0">
            <h3 class="text-lg font-semibold mb-3">Available Time Slots</h3>
            <div class="grid grid-cols-2 gap-2 mb-6">
                <button
                v-for="slot in availableSlots"
                :key="slot.start"
                @click="selectSlot(slot)"
                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition"
                >
                {{ slot.formatted }}
                </button>
            </div>

            <div v-if="selectedSlot" class="mb-4">
                <h3 class="text-lg font-semibold mb-3">Selected Slot: {{ selectedSlot.formatted }}</h3>

                <div class="mb-4">
                    <label class="block text-gray-700 mb-2" for="name">Your Name</label>
                    <input
                        type="text"
                        v-model="form.name"
                        class="w-full px-3 py-2 border rounded-md"
                        required
                    >
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 mb-2" for="email">Your Email</label>
                    <input
                        type="email"
                        v-model="form.email"
                        class="w-full px-3 py-2 border rounded-md"
                        required
                    >
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 mb-2" for="notes">Notes (Optional)</label>
                    <textarea
                        v-model="form.notes"
                        class="w-full px-3 py-2 border rounded-md"
                        rows="3"
                    ></textarea>
                </div>

                <button
                @click="submitBooking"
                class="w-full bg-green-500 text-white py-2 px-4 rounded hover:bg-green-600 transition"
                :disabled="isSubmitting"
                >
                {{ isSubmitting ? 'Booking...' : 'Confirm Booking' }}
                </button>
            </div>
        </div>

        <div v-if="availableSlots.length === 0 && selectedDate && !loading" class="text-center py-4">
        No available slots for this date.
        </div>

        <div v-if="bookingSuccess" class="mt-6 p-4 bg-green-100 text-green-700 rounded">
            <p class="font-semibold">Booking confirmed!</p>
            <p>A confirmation has been sent to your email.</p>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';

const selectedDate = ref('');
const availableSlots = ref([]);
const selectedSlot = ref(null);
const loading = ref(false);
const isSubmitting = ref(false);
const bookingSuccess = ref(false);

const form = ref({
    name: '',
    email: '',
    notes: ''
});

const fetchAvailableSlots = async () => {
    if (!selectedDate.value) return;

    loading.value = true;
    availableSlots.value = [];
    selectedSlot.value = null;

    try {
        const response = await axios.get('/api/available-slots', {
        params: {
            date: selectedDate.value,
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
        }
        });
        availableSlots.value = response.data;
    } catch (error) {
        console.error('Error fetching slots:', error);
    } finally {
        loading.value = false;
  }
};

const selectSlot = (slot) => {
  selectedSlot.value = slot;
};

const submitBooking = async () => {
    if (!form.value.name || !form.value.email) return;

    isSubmitting.value = true;

    try {
        const response = await axios.post('/api/bookings', {
            visitor_name: form.value.name,
            visitor_email: form.value.email,
            description: form.value.notes,
            start_time: selectedSlot.value.start,
            end_time: selectedSlot.value.end,
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
        });

        bookingSuccess.value = true;
        resetForm();
    } catch (error) {
        console.error('Error submitting booking:', error);
    } finally {
        isSubmitting.value = false;
    }
};

const resetForm = () => {
    form.value = {
        name: '',
        email: '',
        notes: ''
    };
    selectedSlot.value = null;
    availableSlots.value = [];
    selectedDate.value = '';
};
</script>
