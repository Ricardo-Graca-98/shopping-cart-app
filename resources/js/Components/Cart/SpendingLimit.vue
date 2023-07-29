<template>
  <div class="bg-white p-4 rounded-lg shadow-md">
    <div class="relative" data-te-input-wrapper-init>
      <label for="limit"
        ><span class="font-bold">Spending Limit: </span>£
      </label>
      <input
        @input="emitSpendingLimit($event.target.value)"
        @change="storeSpendingLimit($event.target.value)"
        v-model="spendingLimit"
        class="w-40"
        type="number"
        id="limit"
        min="0"
        max="9999"
        placeholder="0"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";

const emit = defineEmits(['spendingLimitChanged'])

const localLimitStorageKey = "spending_limit";

const spendingLimit = ref(0);

// Load cart data from localStorage when the component is mounted
onMounted(() => {
  getStoredSpendingLimit();
});

const storeSpendingLimit = (limit) => {
    localStorage.setItem(localLimitStorageKey, limit);
}

const getStoredSpendingLimit = () => {
  const storedSpendingLimit = localStorage.getItem(localLimitStorageKey);
  spendingLimit.value = storedSpendingLimit < 0 ? 0 : storedSpendingLimit;

  emitSpendingLimit(spendingLimit.value);
};

const emitSpendingLimit = (value) => {
    emit('spendingLimitChanged', value)
}
</script>

<style>
</style>