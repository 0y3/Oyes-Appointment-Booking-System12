<script setup>
import { ref, computed ,watch} from 'vue'
import moment from 'moment'
import axios from 'axios';
import { useToast } from 'vue-toastification'
import { useVuelidate } from '@vuelidate/core'
import { required, minLength, maxLength, email as emailValidator } from '@vuelidate/validators'
// import IconCalendar from '@/components/icons/IconCalendar.vue'

// const selectedDate = ref(new Date())
const selectedDate = ref(moment().format('YYYY-MM-DD'));
const availableSlots = ref([]);
const selectedSlot = ref(null);
const minDate = new Date();
const formError = ref('');
const loading = ref(false);
const error = ref('');
const toast = useToast();
const popover = ref(false);

const togglePopover = () => {
  popover.value = !popover.value
}

const form = ref({
  date: selectedDate.value,
  slot: '',
  name: '',
  email: '',
  note: ''
})

// Vuelidate rules
const rules = {
  date: { required },
  slot: { required },
  name: { required, minLength: minLength(2) },
  email: { required, email: emailValidator },
  note: { maxLength: maxLength(500) },
}


const v$ = useVuelidate(rules, form)

// const slots = [
//   '1:00 am', '2:00 am', '3:00 am', '4:00 am',
//   '8:00 am', '9:00 am', '10:00 am', '11:00 am',
//   '12:00 pm', '13:00 pm', '14:00 pm', '15:00 pm',
//   '16:00 pm', '17:00 pm', '18:00 pm',
// ];

const fetchSlots = async () => {
    loading.value = true;
    error.value = '';
    try {
        const date = moment(selectedDate.value).format('YYYY-MM-DD');
        const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
        const res = await axios.get('/api/available-slots', { params: { date, timezone } });
        availableSlots.value = res.data;
    } catch (e) {
        error.value = 'Could not fetch slots. Please try again.';
        availableSlots.value = [];
    } finally {
        loading.value = false;
    }
};
const attrs = ref([
  {
    key: 'today',
    highlight: true,
  },
])

const disabledDates = ref([
  {
    repeat: {
      weekdays:[1,7],
    },
  },
]);

const masks = ref({
  modelValue: 'YYYY-MM-DD',
  input: 'YYYY-MM-DD',
});

watch(selectedDate, fetchSlots, { immediate: true });
const selectSlot = (slot) => {
  selectedSlot.value = slot;
  form.value.slot = moment(slot.start, 'HH:mm').format('h:mm a')
};
// watch(selectedDate, (newVal, oldVal) => {
//   selectedSlot.value = null
// })

const isSlotPast = (slot) =>{
  // slot: e.g. "10:00 am"
  if (!slot || typeof slot !== 'string' || !slot.includes(' ')) return false;
  const today = new Date();
  const selected = typeof selectedDate.value === 'string'? new Date(selectedDate.value): selectedDate.value;

  // Only check for today
  if (
    selected.getFullYear() === today.getFullYear() &&
    selected.getMonth() === today.getMonth() &&
    selected.getDate() === today.getDate()
  ) {
    // Parse slot time
    const [time, meridian] = slot.split(' ');
    let [hours, minutes] = time.split(':').map(Number);
    if (meridian.toLowerCase() === 'pm' && hours !== 12) hours += 12;
    if (meridian.toLowerCase() === 'am' && hours === 12) hours = 0;

    // Create a date for the slot
    const slotDate = new Date(selected);
    slotDate.setHours(hours, minutes, 0, 0);

    // console.log( 'selectedDate.value', selectedDate.value,'slotDate', slotDate)
    // console.log('today', today)
    // console.log('slotDate < today', slotDate < today)

    return slotDate < today;
  }
  return false;
}

const formattedHeader = computed(() => {
  let date = typeof selectedDate.value === 'string'? new Date(selectedDate.value): selectedDate.value;
  if (!(date instanceof Date) || isNaN(date)) return ''
  return moment(date).format('MMMM Do YYYY')
})

// Validation schema using Yup
// const schema = yup.object({
//   date: yup.string().required('Appointment Date is required'),
//   slot: yup.string().required('Time slot is required'),
//   name: yup.string().required('Name is required').min(2, 'Name too short'),
//   email: yup.string().required('Email is required').email('Email must be valid'),
//   note: yup.string().max(500, 'Note too long'),
// })


// Submit booking
// const loading = ref(false)
const submitBooking = async () => {

  const isValid = await v$.value.$validate()
  if (!isValid) {
    formError.value = 'Please fix the errors in the form.'
    return
  }

  if (!selectedSlot.value) {
    formError.value = 'Please select a time slot.'
    return
  }
  formError.value = ''
  
  loading.value = true
  try {
    const datetimeString = `${form.value.date} ${form.value.slot}`
    const start_time = moment(datetimeString, 'YYYY-MM-DD h:mm a').format('YYYY-MM-DD HH:mm:ss')
    const end_time = moment(start_time).add(1, 'hour').format('YYYY-MM-DD HH:mm:ss')

    await axios.post('/api/bookings', {
      start_time,
      end_time,
      visitor_name: form.value.name,
      visitor_email: form.value.email,
      description: form.value.note,
    })
    toast.success('Booking successful!')
    form.value.name = ''
    form.value.email = ''
    form.value.note = ''
    form.value.slot = ''
    selectedSlot.value = null
    await fetchSlots()
  } catch (error) {
    if (error.response?.data?.errors) {
      // Laravel validation errors
      const messages = Object.values(error.response.data.errors).flat().join(' ')
      formError.value = messages
      toast.error(messages)
    } else {
      formError.value = error.response?.data?.message || 'Booking failed. Please try again.'
      toast.error(formError.value)
    }
  } finally {
    loading.value = false
  }
}

</script>

<template>
  <div class="max-w-5xl mx-auto bg-gray-100 p-8 rounded-lg shadow-md mt-10 text-gray-900">
    <div class="text-center text-2xl font-semibold mb-8">
      {{ formattedHeader }}
    </div>
    <!-- <Form @submit="submitBooking" :validation-schema="schema" v-slot="{ errors,setFieldValue, resetForm }"> -->
    <form @submit.prevent="submitBooking" >
      <div class="flex flex-col md:flex-row gap-8">
        <!-- Calendar & Form -->
        <div class="w-full md:w-1/2 space-y-4">
          <label class="block mb-1 font-medium">Appointment Date</label>
          <VDatePicker
            v-model.string="selectedDate"
            :attributes="attrs"
            :first-day-of-week="2"
            :masks="masks"
            :disabled-dates="disabledDates"
            :min-date="minDate"
            :popover="{visibility: 'click'}"
          >
            <template #default="{ inputValue, inputEvents }">
              <div class="flex rounded-lg border border-gray-300 overflow-hidden mb-1 bg-white">
                <button
                  class="flex justify-center items-center px-2 bg-accent-100 hover:bg-accent-200 text-accent-700 border-r border-gray-30"
                  @click="togglePopover"
                  type="button"
                >
                  <IconCalendar class="w-5 h-5" />
                </button>
                <input
                  :value="inputValue"
                  v-on="inputEvents"
                  class="flex-grow px-2 py-1.5 text-sm border-none focus:outline-none"
                  required
                  name="date"
                />
              </div>
            </template>
          </VDatePicker>
          <div class="mb-4">
            <span v-if="v$.date.$error" class="text-red-500 text-xs">
              Appointment Date is required
            </span>
          </div>
          <div>
            <label class="block mb-1 font-medium">Name</label>
            <input
              v-model="form.name"
              class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm bg-white"
              :class="{ 'border-red-500': v$.name.$error }"
            />
            <span class="text-red-500 text-xs" v-for="error of v$.name.$errors" :key="error.$uid">
                <div class="error-msg">Name is required</div>
            </span>
          </div>
          <div>
            <label class="block mb-1 font-medium">Email</label>
            <input
              v-model="form.email"
              type="email"
              class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm bg-white"
              :class="{ 'border-red-500': v$.email.$error }"
            />
            <span class="text-red-500 text-xs" v-for="error of v$.email.$errors" :key="error.$uid">
                <span class="error-msg" v-if="v$.email.required.$invalid"> Email is required</span>
                <span class="error-msg" v-else-if="v$.email.email.$invalid"> Please enter a valid email address</span>
            </span>
          </div>
          <div>
            <label class="block mb-1 font-medium">Note</label>
            <textarea
              v-model="form.note"
              rows="2"
              class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm bg-white"
              :class="{ 'border-red-500': v$.note.$error }"
            />
            <span class="text-red-500 text-xs" v-for="error of v$.note.$errors" :key="error.$uid">
                <div class="error-msg">{{ error.$message }}</div>
            </span>
          </div>
          <div v-if="formError" class="text-red-600 mt-2">{{ formError }}</div>
        </div>
        <!-- Time Slots -->
        <div v-if="loading" class="text-center items-center fixed inset-0 z-50 flex justify-center transition-all duration-500 bg-gray-800/60">
            <div role="status">
                <svg aria-hidden="true" class="inline w-10 h-10 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                    <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                </svg>
                <span class="sr-only text-white">Loading...</span>
            </div>
        </div>
        <div v-if="error" class="mb-2 text-red-600">{{ error }}</div>
        <div v-if="availableSlots.length" class="w-full md:w-1/2 flex flex-col h-full">
          <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2 max-h-64 overflow-y-auto pr-2">
            <button
              v-for="slot in availableSlots"
              :key="slot.start"
              type="button"
              @click="selectSlot(slot)"
              :class="[
                'px-2 py-1 rounded text-sm transition',
                selectedSlot && selectedSlot.start === slot.start
                ? 'bg-gray-600 text-white font-semibold'
                : 'bg-gray-200 hover:bg-gray-400 cursor-pointer',
                ]"
            >{{ moment(slot.start, 'HH:mm').format('h:mm A') }}</button>
          </div>
          <div class="mb-6">
            <span v-if="v$.slot.$error" class="text-red-500 text-xs">Time slot is required.</span>
            <!-- <span class="text-red-500 text-xs" v-for="error of v$.slot.$errors" :key="error.$uid">
                <div class="error-msg">{{ error.$message }}</div>
            </span> -->
          </div>
          <button
            type="submit"
            :disabled="loading"
            class="mt-auto w-full bg-emerald-700 text-white cursor-pointer py-2 rounded font-semibold hover:bg-emerald-600 transition disabled:opacity-90"
          >
            <span v-if="loading">Booking...</span>
            <span v-else>Book Appointment</span>
          </button>
        </div>
        <div v-if="!loading && !error && availableSlots.length === 0" class="text-gray-500">No available slots for this date.</div>
      </div>
    </form>
  </div>
</template>

