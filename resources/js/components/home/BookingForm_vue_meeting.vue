
<script setup>
import { ref, computed ,onMounted } from 'vue'
import VueMeetingSelector from 'vue-meeting-selector'
import 'vue-meeting-selector/dist/style.css'
import slotsGenerator from 'vue-meeting-selector/src/helpers/slotsGenerator.js';


// export default {
//   components: { VueMeetingSelector },
//   setup() {
    const selectedDate = ref(new Date())
    const selectedSlot = ref(null)
    const disabledDates = [] // Add dates to disable if needed

    const slots = [
        '8:00 am', '9:00 am', '10:00 am', '11:00 am',
        '12:00 pm', '1:00 pm', '2:00 pm', '3:00 pm',
        '4:00 pm', '5:00 pm', '6:00 pm'
    ]

    function onMeetingSelect({ date }) {
        selectedDate.value = new Date(date)
    }

    const formattedHeader = computed(() => {
        const date = selectedDate.value
        const options = { month: 'long', day: 'numeric', year: 'numeric' }
        return `${date.toLocaleDateString(undefined, options)}`
    })

    const date = ref(new Date());
    const meetingsDays = ref([]);
    const meeting = ref(null);
    const loading = ref(true);

    const nbDaysToDisplay = computed(() => 5);

    // because of line-height, font-type you might need to change top value
    const classNames = computed(() => ({
      tabLoading: 'loading-div',
    }));

    const slotsGeneratorAsync = (d, n, start, end, timesBetween) => {
    return new Promise((resolve) => {
        setTimeout(() => {
        resolve(slotsGenerator(d, n, start, end, timesBetween));
        }, 1000);
    });
    };

    const nextDate = async () => {
      loading.value = true;
      const start = {
        hours: 8,
        minutes: 0,
     };
     const end = {
        hours: 16,
        minutes: 0,
      };
      const dateCopy = new Date(date.value);
      const newDate = new Date(dateCopy.setDate(dateCopy.getDate() + 7));
      date.value = newDate;
      meetingsDays.value = await slotsGeneratorAsync(
        newDate,
        nbDaysToDisplay.value,
        start,
        end,
        30,
      );
      loading.value = false;
    };

    const previousDate = async () => {
        loading.value = true;
        const start = {
            hours: 8,
            minutes: 0,
        };
        const end = {
            hours: 16,
            minutes: 0,
        };

        const dateCopy = new Date(date.value);
        dateCopy.setDate(dateCopy.getDate() - 7);
        const formatingDate = (dateToFormat) => {
            const d = new Date(dateToFormat);
            const day = d.getDate() < 10 ? `0${d.getDate()}` : d.getDate();
            const month = d.getMonth() + 1 < 10 ? `0${d.getMonth() + 1}` : d.getMonth() + 1;
            const year = d.getFullYear();
            return `${year}-${month}-${day}`;
        };
        const newDate = formatingDate(new Date()) >= formatingDate(dateCopy)
            ? new Date()
            : new Date(dateCopy);
        date.value = newDate;
        meetingsDays.value = await slotsGeneratorAsync(
            newDate,
            nbDaysToDisplay.value,
            start,
            end,
            30,
        );
        loading.value = false;
    };


    onMounted(async () => {
      const start = { hours: 8, minutes: 0, };
      const end = { hours: 17, minutes: 0,};
      meetingsDays.value = await slotsGeneratorAsync(
        date.value,
        nbDaysToDisplay.value,
        start,
        end,
        60,
      );
      loading.value = false;
    });

</script>


<template>
  <div class="max-w-5xl mx-auto bg-white p-8 rounded-lg shadow-md mt-10 text-gray-800">
    <div class="text-center text-2xl font-semibold mb-8">
      {{ formattedHeader }}
    </div>
    <div class="flex flex-col md:flex-row gap-8">
      <!-- Calendar (using vue-meeting-selector) -->
      <div class="w-full md:w-1/2">
        <VueMeetingSelector
            class="simple-example__meeting-selector"
            v-model="meeting"
            :date="date"
            :loading="loading"
            :class-names="classNames"
            :meetings-days="meetingsDays"
            @next-date="nextDate"
            @previous-date="previousDate"
        />
      </div>
      <!-- Time Slots -->
      <div class="w-full md:w-1/2">
        <div>
          <div class="grid grid-cols-4 gap-2 max-h-64 overflow-y-auto pr-2">
            <button
              v-for="slot in slots"
              :key="slot"
              @click="selectedSlot = slot"
              :class="[
                'px-2 py-1 rounded text-sm',
                selectedSlot === slot
                  ? 'bg-purple-600 text-white font-semibold'
                  : 'bg-gray-100 hover:bg-purple-100'
              ]"
            >{{ slot }}</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
