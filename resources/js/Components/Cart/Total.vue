<template>
  <div
    class="bg-white p-4 rounded-lg shadow-md flex justify-center items-center"
  >
    <p>
      <span class="font-bold">Total Price: </span>£{{
        totalCartPrice.toFixed(2)
      }}
    </p>
    <svg
      v-if="shouldAlert"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
      stroke-width="1.5"
      stroke="currentColor"
      class="w-6 h-6 ml-2"
      :class="[overLimit ? 'fill-red-500' : 'fill-yellow-500']"
    >
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0M3.124 7.5A8.969 8.969 0 015.292 3m13.416 0a8.969 8.969 0 012.168 4.5"
      />
    </svg>
  </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    totalCartPrice: {
        type: Number,
        required: true,
        default: 0
    },
    spendingLimit: {
        type: Number,
        required: false,
        default: 0
    }
})

const shouldAlert = computed(() => {
    return props.spendingLimit > 0 ? (props.totalCartPrice > (props.spendingLimit * 0.8)) : false; // We want to be alert at 80%
})

const overLimit = computed(() => {
    return props.totalCartPrice > props.spendingLimit;
})
</script>

<style>
</style>