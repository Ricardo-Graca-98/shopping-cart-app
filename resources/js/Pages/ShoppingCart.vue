<template>
  <Head title="Cart" />

  <div
    class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white"
  >
    <div class="sm:fixed sm:top-0 sm:right-0 p-6 text-right">
      <Link
        class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
        v-if="$page.props.auth.user"
        :href="route('logout')"
        method="post"
        as="button"
      >
        Log Out
      </Link>

      <template v-else>
        <Link
          :href="route('login')"
          class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
          >Log in</Link
        >

        <Link
          :href="route('register')"
          class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
          >Register</Link
        >
      </template>
    </div>

    <div class="flex flex-col">
      <div
        class="grid grid-flow-row-dense grid-rows-2 sm:grid-cols-11 gap-4 mb-3"
      >
        <!-- Product list -->
        <div
          class="bg-white p-4 rounded-lg shadow-md max-h-[400px] overflow-y-auto col-span-5"
        >
          <h2 class="text-xl font-bold mb-4">Available Products</h2>
          <hr class="border-4 border-blue-500 mb-4" />
          <ul>
            <li
              v-for="product in availableProducts"
              :key="product.uuid"
              :class="{ 'in-cart': isProductInCart(product) }"
            >
              <div class="flex justify-between items-center mb-2">
                <div class="mr-5">
                  <span class="text-lg font-medium">{{ product.title }}</span>
                  <span class="text-gray-500 ml-2">£{{ product.price }}</span>
                </div>
                <button
                  @click="addToCart(product)"
                  class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition duration-300"
                >
                  +
                </button>
              </div>
            </li>
          </ul>
        </div>

        <!-- Shopping cart list -->
        <div class="bg-white p-4 rounded-lg shadow-md col-span-5">
          <h2 class="text-xl font-bold mb-4">Shopping Cart</h2>
          <hr class="border-4 border-green-500 mb-4" />
          <draggable
            :list="cart"
            @start="dragging = true"
            @end="dragging = false"
            item-key="uuid"
          >
            <template #item="{ element }">
              <div class="flex justify-between items-center mb-2">
                <div class="mr-5" @click="element.pickedUp = !element.pickedUp">
                  <span
                    class="text-lg font-medium"
                    :class="{ 'line-through': element.pickedUp }"
                    >{{ element.title }}</span
                  >
                  <span class="text-gray-500 ml-2">£{{ element.price }}</span>
                  <span v-if="element.quantity > 1" class="text-gray-500 ml-2"
                    >x{{ element.quantity }}</span
                  >
                </div>
                <button
                  @click="removeFromCart(element)"
                  class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition duration-300"
                >
                  -
                </button>
              </div>
            </template>
          </draggable>
        </div>
        <div class="max-h-min max-w-max flex flex-col">
          <button
            class="max-h-min max-w-max bg-white p-4 rounded-lg shadow-md mb-3"
            @click="sendListViaEmail"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
              class="w-6 h-6"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"
              />
            </svg>
          </button>
          <button
            class="max-h-min max-w-max bg-white p-4 rounded-lg shadow-md"
            @click="saveList"
            v-if="$page.props.auth.user"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
              class="w-6 h-6"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z"
              />
            </svg>
          </button>
        </div>
        <!-- Spending Limit -->
        <SpendingLimit
          @spendingLimitChanged="updateSpendingLimit"
          class="col-span-2 h-20 items-center justify-items-center"
        />

        <div class="col-span-6"></div>

        <!-- Cart Total -->
        <CartTotal
          v-bind:total-cart-price="totalCartPrice"
          v-bind:spending-limit="spendingLimit"
          class="col-span-2 h-20 items-center justify-items-center"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { ref, onMounted, watch, computed } from "vue";
import draggable from "vuedraggable";
import CartTotal from "@/Components/Cart/Total.vue";
import SpendingLimit from "@/Components/Cart/SpendingLimit.vue";

const props = defineProps({
  availableProducts: {
    type: Object,
    required: true,
  },
  user: {
    type: Object,
    required: false,
  },
});

const localCartStorageKey = "shopping_cart";
const cart = ref([]);
const spendingLimit = ref(0);

// Load cart data from localStorage when the component is mounted
onMounted(() => {
  getStoredCart();
});

const saveList = () => {
  axios
    .post(route("cart.store"), {
      body: {
        cart: JSON.stringify(cart.value),
        userId: props.user.id,
      },
    })
    .then((response) => {
      if (response.status === 200) {
        alert("Cart saved! :)");

        return;
      }

      alert("Whoops, something went wrong!");
    });
};

const getStoredCart = () => {
  if (!props.user) {
    getCartFromStorage();
    return;
  } else {
    axios
      .get(route("cart.show"), {
        params: {
          userId: props.user.id,
        },
      })
      .then((response) => {
        if (response.status === 200) {
          response.data.forEach((item) => {
            item.quantity = item.pivot.quantity;
            delete item.pivot;

            cart.value.push(item);
          });

          if (cart.value.length === 0) getCartFromStorage();

          return;
        } else {
          alert("Whoops, something went wrong when retrieving your cart!");
        }
      });
  }
};

const getCartFromStorage = () => {
  const storedCart = JSON.parse(localStorage.getItem(localCartStorageKey));

  if (storedCart.length > 0) {
    storedCart.forEach((item) => cart.value.push(item));
  }
};

const updateSpendingLimit = (limit) => {
  spendingLimit.value = Number(limit);
};

const totalCartPrice = computed(() => {
  return cart.value.reduce(
    (accumulator, cartItem) => accumulator + cartItem.price * cartItem.quantity,
    0
  );
});

// Check if product is in the cart
const isProductInCart = (product) => {
  return cart.value.some((item) => item.id === product.id);
};

// Add items to the cart
const addToCart = (product) => {
  const cartItemIndex = cart.value.findIndex((item) => item.id === product.id);
  if (cartItemIndex !== -1) {
    cart.value[cartItemIndex].quantity++;
  } else {
    cart.value.push({ ...product, quantity: 1, pickedUp: false });
  }
};

// Remove items from the cart
const removeFromCart = (cartItem) => {
  const cartItemIndex = cart.value.findIndex((item) => item.id === cartItem.id);
  if (cartItemIndex !== -1) {
    if (cart.value[cartItemIndex].quantity > 1) {
      cart.value[cartItemIndex].quantity--;
    } else {
      cart.value.splice(cartItemIndex, 1);
    }
  }
};

const sendListViaEmail = () => {
  if (cart.value.length <= 0) {
    alert("Cart is empty!");

    return;
  }

  let email = prompt("Enter your email address:");

  if (!validateEmail(email)) {
    alert("Invalid email address.");

    return;
  }

  axios
    .post(route("cart.email.send"), {
      body: {
        email: email,
        cart: JSON.stringify(cart.value),
      },
    })
    .then((response) => {
      if (response.status === 200) {
        alert("Shopping list sent! :)");

        return;
      }

      alert("Whoops, something went wrong!");
    });
};

const validateEmail = (email) => {
  if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)) {
    return true;
  }

  return false;
};

// Watch for changes in the 'cart' array and save to localStorage
watch(cart.value, () => {
  localStorage.setItem(localCartStorageKey, JSON.stringify(cart.value));
});
</script>

<style>
.bg-dots-darker {
  background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(0,0,0,0.07)'/%3E%3C/svg%3E");
}
@media (prefers-color-scheme: dark) {
  .dark\:bg-dots-lighter {
    background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(255,255,255,0.07)'/%3E%3C/svg%3E");
  }
}
.in-cart {
  background-color: #f7f7f7;
}
</style>
