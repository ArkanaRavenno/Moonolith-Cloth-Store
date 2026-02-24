<script setup>
import Navbar from '../../components/Navbar.vue';
import FlashSale from '../../components/landing/FlashSale.vue';
import FlashSaleContainer from '../../components/landing/FlashSaleContainer.vue';
import HeroCarousel from '../../components/landing/HeroCarousel.vue';
import ProductCard from '../../components/ProductCard.vue';
import Footer from '../../components/Footer.vue';
import { useRoute } from 'vue-router';
import carousel1 from '@/assets/carousel1.jpg';
import carousel2 from '@/assets/carousel2.jpg';
import carousel3 from '@/assets/carousel3.jpg';
import FlashSaleSkeleton from '@/components/skeletons/FlashSaleSkeleton.vue';
import ProductCardSkeleton from '@/components/skeletons/ProductCardSkeleton.vue';
import FlashSaleEmpty from '@/components/landing/FlashSaleEmpty.vue';
import { ref, onMounted, computed, nextTick } from 'vue';
import api from '@/services/api';
import { MessageCircle, X, Send, Loader2, User } from 'lucide-vue-next';

const flashSale = ref(null);
const flashSaleProducts = ref([]);
const rawProducts = ref([]);
const loading = ref(true);
const isLoggedIn = ref(false);
const showChatModal = ref(false);
const chatRoom = ref(null);
const messages = ref([]);
const newMessage = ref('');
const isSending = ref(false);
const isLoadingChat = ref(false);

onMounted(async () => {
  const token = localStorage.getItem('token');
  isLoggedIn.value = !!token;

  loading.value = true;
  await Promise.all([
    fetchFlashSaleStatus(),
    fetchProducts()
  ]);
  loading.value = false;
  setTimeout(() => {
    loading.value = false;
  }, 300);
});

const heroImages = ref([
  carousel1,
  carousel2,
  carousel3
]);

const fetchFlashSaleStatus = async () => {
  try {
    const res = await api.get('/public/flash-sale');
    if (!res.data.data) {
      flashSale.value = null;
      flashSaleProducts.value = [];
      return;
    }
    flashSale.value = res.data.data;
    const detail = await api.get(`/public/flash-sale/${flashSale.value.id}`);
    flashSaleProducts.value = detail.data.data.items.map(item => ({
      id: item.id,
      name: item.name,
      image_url: item.image_url,
      price: item.original_price,
      flash_sale: {
        discount_price: item.discount_price,
        stock: item.flash_sale_stock,
        sold: item.sold_count,
        remaining: item.flash_sale_stock - item.sold_count
      }
    }));
  } catch (e) {
    console.error("Error fetching flash sale:", e);
    flashSale.value = null;
    flashSaleProducts.value = [];
  }
};

const fetchProducts = async () => {
  try {
    const res = await api.get('/public/items');
    rawProducts.value = res.data.data;
  } catch (e) {
    console.error("Error fetching products:", e);
  }
};

const products = computed(() => {
  return rawProducts.value.map(item => {
    const flashItem = flashSaleProducts.value.find(fs => fs.id === item.id);
    if (flashItem) {
      const remainingStock = flashItem.flash_sale.remaining;
      return {
        ...item,
        price: item.price,
        isFlashSaleSoldOut: remainingStock <= 0,
        isGlobalSoldOut: item.total_stock <= 0,
        flash_sale: {
          discount_price: flashItem.flash_sale.discount_price,
          stock: flashItem.flash_sale.stock,
          sold: flashItem.flash_sale.sold,
          remaining: remainingStock
        }
      };
    }
    return {
      ...item,
      flash_sale: null,
      isFlashSaleSoldOut: false,
      isGlobalSoldOut: item.total_stock <= 0
    };
  });
});

const handleFlashSaleExpired = async () => {
  await Promise.all([
    fetchFlashSaleStatus(),
    fetchProducts()
  ]);
};

const hasFlashSaleStock = computed(() => {
  if (!Array.isArray(flashSaleProducts.value)) return false;
  return flashSaleProducts.value.some(p => p.flash_sale.remaining > 0);
});

const hasProducts = computed(() => products.value && products.value.length > 0);
const hasFlashSale = computed(() => flashSale.value !== null);

const openChat = async () => {
  showChatModal.value = true;
  isLoadingChat.value = true;
  try {
    const res = await api.get('/user/chat/room', { order_id: null });
    chatRoom.value = res.data.data;
    await fetchMessages();
  } catch (e) {
    console.error('Gagal membuka chat', e);
  } finally {
    isLoadingChat.value = false;
  }
};

const fetchMessages = async () => {
  if (!chatRoom.value) return;
  await nextTick();
  scrollToBottom();
  try {
    const res = await api.get(`/user/chat/${chatRoom.value.id}/messages`);
    messages.value = res.data.data;
    scrollToBottom();
  } catch (e) {
    console.error('Gagal fetch messages', e);
  }
};

const sendMessage = async () => {
  if (!newMessage.value.trim()) return;

  if (!chatRoom.value) {
    console.error('Chat room belum ada');
    return;
  }

  isSending.value = true;

  try {
    await api.post('/user/chat/send', {
      chat_id: chatRoom.value.id,
      message: newMessage.value
    });

    newMessage.value = '';

    await fetchMessages();

  } catch (e) {
    console.error('Gagal kirim pesan', e);
  } finally {
    isSending.value = false;
    scrollToBottom();
  }
};

const scrollToBottom = () => {
  nextTick(() => {
    const container = document.querySelector('.chat-scroll');
    if (container) {
      container.scrollTop = container.scrollHeight;
    }
  });
};

const toggleChat = async () => {
  showChatModal.value = !showChatModal.value;

  if (showChatModal.value && !chatRoom.value) {
    await openChat();
  }
};

</script>

<template>
  <div class="landing-page-wrapper bg-gray-50 min-h-screen font-sans text-slate-900 relative">
    <Navbar />

    <section class="w-full">
      <HeroCarousel :images="heroImages" />
    </section>

    <div class="mt-2 md:mt-6 bg-gray-100 border-y border-gray-100 py-6 md:py-10">
      <FlashSaleSkeleton v-if="loading" />
      <template v-else>
        <FlashSale v-if="flashSale" :isActive="true" :startTime="flashSale.start_time" :endTime="flashSale.end_time"
          @expired="handleFlashSaleExpired" />
        <FlashSaleContainer v-if="hasFlashSale && hasFlashSaleStock" :products="flashSaleProducts" />
        <div v-else-if="flashSale && !hasFlashSaleStock" class="py-10 text-center">
          <div class="text-3xl mb-2">😢</div>
          <p class="font-bold text-gray-600">Semua produk Flash Sale sudah habis</p>
          <p class="text-sm text-gray-400 mt-1">Tunggu promo berikutnya ya!</p>
        </div>
        <FlashSaleEmpty v-if="!flashSale" />
      </template>
    </div>

    <section class="max-w-7xl mx-auto px-4 my-8 md:my-16">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
        <div class="relative rounded-2xl md:rounded-3xl overflow-hidden group cursor-pointer h-48 md:h-87.5 shadow-md">
          <img src="@/assets/banner-pria.jpg"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" />
          <div
            class="absolute inset-0 bg-linear-to-r from-blue-900/90 via-blue-900/40 to-transparent flex flex-col justify-center p-6 md:p-8">
            <h3 class="text-white text-xl md:text-4xl font-black mb-1 md:mb-2 uppercase leading-tight">Koleksi<br>Pria
            </h3>
            <p class="text-blue-100 text-[10px] md:text-sm mb-3 md:mb-4 opacity-90">Gaya urban minimalis.</p>
            <router-link :to="{ name: 'MenPages' }">
              <button
                class="bg-white text-blue-900 font-bold px-5 py-1.5 md:px-6 md:py-2 rounded-full w-fit text-[10px] md:text-sm hover:bg-blue-900 hover:text-white transition-colors cursor-pointer shadow-lg">
                Explore
              </button>
            </router-link>
          </div>
        </div>

        <div v-if="isLoggedIn"
          class="relative rounded-2xl md:rounded-3xl overflow-hidden group bg-linear-to-br from-slate-900 via-blue-900 to-slate-900 shadow-xl border border-blue-800 h-48 md:h-87.5">
          <div class="absolute inset-0 opacity-10 pattern-grid-lg pointer-events-none"></div>
          <div
            class="absolute -bottom-5 -left-5 w-24 h-24 md:w-40 md:h-40 bg-blue-500/20 rounded-full blur-2xl md:blur-3xl">
          </div>
          <div class="relative z-10 flex flex-col items-center justify-center h-full text-center p-6 md:p-8">
            <span
              class="bg-amber-500 text-white text-[8px] md:text-[10px] font-black px-2 py-0.5 md:px-3 md:py-1 rounded-full uppercase tracking-widest mb-2 shadow-lg animate-pulse">
              Most Wanted
            </span>
            <h3 class="text-white text-xl md:text-4xl font-black mb-1 md:mb-2 uppercase leading-tight tracking-tighter">
              Best <span class="text-amber-400">Seller</span>
            </h3>
            <p class="text-blue-100 text-[10px] md:text-sm mb-4 md:mb-6 px-4 leading-relaxed">
              Koleksi terpopuler <br class="md:hidden"> komunitas Moonolith.
            </p>
            <router-link :to="{ name: 'MostBuyPages' }" class="inline-block z-20">
              <button
                class="bg-amber-500 text-white px-6 py-2 md:px-8 md:py-3 rounded-full font-extrabold hover:bg-amber-400 hover:scale-105 active:scale-95 transition-all shadow-lg cursor-pointer border-none uppercase text-[10px] md:text-xs tracking-widest">
                LIHAT SEMUA
              </button>
            </router-link>
          </div>
        </div>

        <div v-else
          class="relative rounded-2xl md:rounded-3xl overflow-hidden group bg-blue-900 shadow-xl border border-blue-800 h-48 md:h-87.5">
          <div class="absolute inset-0 opacity-20 pattern-dots pointer-events-none"></div>
          <div class="relative z-10 flex flex-col items-center justify-center h-full text-center p-6 md:p-8">
            <h3 class="text-white text-xl md:text-3xl font-black mb-1 md:mb-2 uppercase italic leading-tight">Pesan
              Baju<br>Idamanmu</h3>
            <p class="text-blue-200 text-[10px] md:text-sm mb-4 md:mb-6 px-4">Ganti style mu yang <br class="md:hidden">
              biasa-biasa aja</p>
            <router-link :to="{ name: 'Register' }" class="inline-block z-20">
              <button
                class="bg-white text-blue-900 px-6 py-2 md:px-8 md:py-3 rounded-full font-extrabold hover:bg-blue-50 hover:scale-105 transition-all shadow-lg cursor-pointer text-[10px] md:text-sm">
                GABUNG SEKARANG
              </button>
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <main class="max-w-7xl mx-auto px-4 pb-20">
      <section class="mt-10 md:mt-16">
        <div class="text-center mb-12">
          <h2 class="text-3xl md:text-4xl font-black text-slate-900 tracking-tight uppercase">
            Koleksi <span class="text-blue-900">Terbaru</span>
          </h2>
          <div class="w-20 h-1.5 bg-blue-900 mx-auto mt-4 rounded-full"></div>
        </div>

        <div v-if="!loading && !hasProducts" class="py-20 text-center">
          <div class="text-5xl mb-4">📦</div>
          <h3 class="text-lg font-bold text-gray-700">Belum ada produk</h3>
          <p class="text-sm text-gray-400 mt-2">Produk akan segera tersedia.</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3 md:gap-6">
          <template v-if="loading">
            <ProductCardSkeleton v-for="i in 4" :key="i" />
          </template>
          <ProductCard v-else v-for="item in products" :key="item.id" :product="item" />
        </div>
      </section>

      <div class="flex justify-center mt-12 px-4">
        <router-link :to="{ name: 'Catalog' }">
          <button
            class="w-full md:w-auto bg-blue-800 text-white px-12 py-4 rounded-xl font-bold shadow-lg active:scale-95 transition-transform hover:cursor-pointer">
            LIHAT PRODUK LAINNYA
          </button>
        </router-link>
      </div>
    </main>

    <Footer />

    <div v-if="isLoggedIn" class="fixed bottom-6 right-6 z-50 flex flex-col items-end gap-2">
      <transition name="fade">
        <div v-if="!showChatModal" class="bg-white px-4 py-2 rounded-lg shadow-lg border border-gray-100 mb-2 mr-1">
          <p class="text-xs font-semibold text-gray-700">Butuh bantuan?</p>
        </div>
      </transition>

      <button @click="toggleChat"
        class="group bg-blue-700 hover:bg-blue-800 text-white w-14 h-14 md:w-16 md:h-16 rounded-full shadow-2xl flex items-center justify-center transition-all duration-300 hover:scale-110 active:scale-95 relative overflow-hidden">
        <span
          class="absolute inset-0 bg-white/20 rounded-full scale-0 group-hover:scale-150 transition-transform duration-500"></span>
        <X v-if="showChatModal" class="w-7 h-7 relative z-10" />
        <MessageCircle v-else class="w-7 h-7 relative z-10" />
      </button>
    </div>

    <transition name="slide-up">
      <div v-if="showChatModal"
        class="fixed bottom-24 right-4 md:right-8 z-40 w-[90vw] md:w-96 h-[60vh] md:h-125 bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden ring-1 ring-black/5 transform origin-bottom-right">

        <div
          class="bg-linear-to-r from-blue-800 to-blue-600 p-4 flex items-center justify-between shrink-0 shadow-md z-10">
          <div class="flex items-center gap-3">
            <div class="relative">
              <div class="bg-white/10 p-2 rounded-full">
                <User class="w-5 h-5 text-white" />
              </div>
              <span
                class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-400 border-2 border-blue-700 rounded-full"></span>
            </div>
            <div>
              <h4 class="font-bold text-white text-sm">Customer Service</h4>
              <p class="text-blue-100 text-[10px] flex items-center gap-1">
                <span class="w-1 h-1 bg-green-400 rounded-full animate-pulse"></span> Online
              </p>
            </div>
          </div>
          <button @click="showChatModal = false"
            class="text-white/80 hover:text-white hover:bg-white/10 p-1 rounded transition-colors">
            <X class="w-5 h-5" />
          </button>
        </div>

        <div class="flex-1 bg-slate-50 relative flex flex-col overflow-hidden">
          <div class="absolute inset-0 opacity-[0.03] pattern-dots pointer-events-none"></div>

          <div v-if="isLoadingChat" class="flex-1 flex flex-col items-center justify-center text-slate-400 gap-2">
            <Loader2 class="w-8 h-8 animate-spin text-blue-500" />
            <span class="text-xs">Memuat percakapan...</span>
          </div>

          <div v-else class="flex-1 overflow-y-auto p-4 space-y-4 chat-scroll z-0">
            <div v-if="messages.length === 0" class="text-center mt-8">
              <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
                <MessageCircle class="w-8 h-8 text-blue-600" />
              </div>
              <p class="text-slate-600 text-sm font-medium">Halo! Ada yang bisa kami bantu?</p>
              <p class="text-slate-400 text-xs mt-1">Tulis pesan Anda di bawah ini.</p>
            </div>

            <div v-for="msg in messages" :key="msg.id" class="flex flex-col max-w-[85%]"
              :class="[msg.sender_id === chatRoom?.user_id ? 'self-end items-end' : 'self-start items-start']">

              <span v-if="msg.sender_id !== chatRoom?.user_id" class="text-[10px] text-slate-400 ml-1 mb-1 block">
                {{ msg.sender?.name || 'Admin' }}
              </span>

              <div class="px-4 py-2.5 shadow-sm text-sm wrap-break-words relative" :class="[
                msg.sender_id === chatRoom?.user_id
                  ? 'bg-blue-600 text-white rounded-2xl rounded-tr-sm'
                  : 'bg-white text-slate-800 border border-slate-100 rounded-2xl rounded-tl-sm'
              ]">
                {{ msg.message }}
              </div>

              <span class="text-[9px] text-slate-400 mt-1 mx-1 opacity-70">
                {{ msg.created_at ? new Date(msg.created_at).toLocaleTimeString([], {
                  hour: '2-digit', minute: '2-digit'
                })
                  :
                'Just now' }}
              </span>
            </div>
          </div>
        </div>

        <div class="p-3 bg-white border-t border-slate-100 z-10 shrink-0">
          <form @submit.prevent="sendMessage"
            class="flex gap-2 items-center bg-slate-100 rounded-full px-2 py-2 border border-transparent focus-within:border-blue-300 focus-within:ring-2 focus-within:ring-blue-100 transition-all">

            <input v-model="newMessage" placeholder="Ketik pesan..."
              class="flex-1 bg-transparent border-none outline-none focus:outline-none focus:ring-0 text-sm px-3 text-slate-700 placeholder:text-slate-400"
              :disabled="isSending" />

            <button type="submit" :disabled="isSending || !newMessage.trim()"
              class="w-9 h-9 flex items-center justify-center rounded-full transition-all duration-200"
              :class="newMessage.trim() && !isSending ? 'bg-blue-600 text-white hover:bg-blue-700 shadow-md hover:scale-105' : 'bg-slate-200 text-slate-400 cursor-not-allowed'">
              <Loader2 v-if="isSending" class="w-4 h-4 animate-spin" />
              <Send v-else class="w-4 h-4 ml-0.5" />
            </button>
          </form>
        </div>

      </div>
    </transition>
  </div>
</template>

<style scoped>
button {
  position: relative;
  overflow: hidden;
}

button:after {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.3), transparent);
  transform: rotate(45deg);
  transition: 0.7s;
}

button:hover:after {
  left: 100%;
  top: 100%;
}

.chat-scroll::-webkit-scrollbar {
  width: 5px;
}

.chat-scroll::-webkit-scrollbar-track {
  background: transparent;
}

.chat-scroll::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 20px;
}

.chat-scroll::-webkit-scrollbar-thumb:hover {
  background-color: #94a3b8;
}

.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.slide-up-enter-from,
.slide-up-leave-to {
  transform: translateY(20px) scale(0.95);
  opacity: 0;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.pattern-dots {
  background-image: radial-gradient(#475569 1px, transparent 1px);
  background-size: 20px 20px;
}
</style>