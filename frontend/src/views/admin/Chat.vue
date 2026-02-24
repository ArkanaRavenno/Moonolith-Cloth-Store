<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { useRouter } from 'vue-router'; // Import router
import api from '@/services/api';
import { 
  Send, Search, MessageSquare, MoreVertical, 
  Paperclip, ArrowLeft, Check, Clock 
} from 'lucide-vue-next';

const router = useRouter(); // Init router
const rooms = ref([]);
const selectedRoom = ref(null);
const messages = ref([]);
const newMessage = ref('');
const isLoading = ref(false);
const isSending = ref(false);
const chatContainer = ref(null);

// ================= NAVIGASI =================
const goBack = () => {
  router.push({ name: 'Dashboard' });
};

// ================= LOAD ROOMS =================
const fetchRooms = async () => {
  try {
    const res = await api.get('/admin/chat/rooms');
    rooms.value = res.data.data;
  } catch (e) {
    console.error("Gagal memuat room:", e);
  }
};

// ================= SCROLL HELPER =================
const scrollToBottom = async () => {
  await nextTick();
  if (chatContainer.value) {
    chatContainer.value.scrollTo({
      top: chatContainer.value.scrollHeight,
      behavior: 'smooth'
    });
  }
};

// ================= LOAD MESSAGES =================
const openRoom = async (room) => {
  selectedRoom.value = room;
  isLoading.value = true;

  try {
    const res = await api.get(`/admin/chat/${room.id}/messages`);
    messages.value = res.data.data;
    scrollToBottom();
  } catch (e) {
    console.error("Gagal memuat pesan:", e);
  } finally {
    isLoading.value = false;
  }
};

// ================= SEND =================
const sendMessage = async () => {
  if (!newMessage.value.trim()) return;
  isSending.value = true;

  try {
    await api.post('/admin/chat/send', {
      chat_id: selectedRoom.value.id,
      message: newMessage.value
    });

    newMessage.value = '';
    await openRoom(selectedRoom.value);
  } catch (e) {
    console.error("Gagal kirim:", e);
  } finally {
    isSending.value = false;
    // Paksa fokus kembali ke input setelah kirim
    document.getElementById('chatInput')?.focus();
  }
};

onMounted(fetchRooms);
</script>

<template>
  <div class="flex h-[calc(100vh-2rem)] bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-200">

    <div class="w-80 md:w-96 flex flex-col border-r border-gray-100 bg-white z-20">
      
      <div class="p-4 border-b border-gray-100 bg-gray-50/50">
        <div class="flex items-center gap-3 mb-4">
          <button @click="goBack" 
            class="p-2 rounded-full bg-white border border-gray-200 text-gray-600 hover:bg-gray-100 hover:text-blue-600 transition-all shadow-sm group"
            title="Kembali ke Dashboard">
            <ArrowLeft class="w-5 h-5 group-hover:-translate-x-1 transition-transform" />
          </button>
          <h2 class="font-bold text-xl text-gray-800 tracking-tight">Pesan</h2>
        </div>

        <div class="relative">
          <Search class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" />
          <input type="text" placeholder="Cari percakapan..." 
            class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-gray-700 placeholder:text-gray-400 shadow-sm">
        </div>
      </div>

      <div class="flex-1 overflow-y-auto custom-scrollbar bg-white">
        <div v-if="rooms.length === 0" class="text-center py-10 text-gray-400 text-sm flex flex-col items-center">
          <MessageSquare class="w-8 h-8 mb-2 opacity-20" />
          <span>Belum ada chat masuk</span>
        </div>

        <div v-for="room in rooms" :key="room.id" @click="openRoom(room)"
          class="flex items-center gap-4 p-4 cursor-pointer transition-all duration-200 border-b border-gray-50 hover:bg-blue-50/30 relative group"
          :class="selectedRoom?.id === room.id ? 'bg-blue-50 border-r-4 border-r-blue-600' : ''">
          
          <div class="relative">
            <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-md shrink-0 ring-2 ring-white">
              {{ room.user.name.charAt(0).toUpperCase() }}
            </div>
            <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></span>
          </div>

          <div class="flex-1 min-w-0">
            <div class="flex justify-between items-center mb-1">
              <h3 class="font-semibold text-gray-800 truncate text-sm">{{ room.user.name }}</h3>
              <span class="text-[10px] text-gray-400 font-medium">Recently</span>
            </div>
            <p class="text-xs text-gray-500 truncate group-hover:text-blue-600 transition-colors">
              Klik untuk melihat pesan...
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="flex-1 flex flex-col bg-[#F2F4F7] relative">
      
      <div v-if="!selectedRoom" class="flex-1 flex flex-col items-center justify-center text-gray-400 select-none bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]">
        <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center mb-6 shadow-xl ring-4 ring-gray-50">
          <MessageSquare class="w-10 h-10 text-blue-500" />
        </div>
        <h3 class="text-xl font-bold text-gray-700">Admin Support Center</h3>
        <p class="text-sm text-gray-500 mt-2 max-w-xs text-center">Pilih pelanggan dari daftar di sebelah kiri untuk mulai membalas pesan.</p>
      </div>

      <template v-else>
        <div class="h-18 px-6 py-3 border-b border-gray-200 bg-white/90 backdrop-blur-md flex items-center justify-between shadow-sm z-10 sticky top-0">
          <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-700 to-gray-900 flex items-center justify-center text-white font-bold shadow-md">
              {{ selectedRoom.user.name.charAt(0) }}
            </div>
            <div>
              <div class="font-bold text-gray-800 text-base leading-tight">{{ selectedRoom.user.name }}</div>
              <div class="flex items-center gap-1.5 mt-0.5">
                <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                <span class="text-xs text-gray-500 font-medium">Online</span>
              </div>
            </div>
          </div>
          <div class="flex gap-2">
            <button class="p-2 hover:bg-gray-100 rounded-full text-gray-500 transition-colors">
               <Search class="w-5 h-5" />
            </button>
            <button class="p-2 hover:bg-gray-100 rounded-full text-gray-500 transition-colors">
              <MoreVertical class="w-5 h-5" />
            </button>
          </div>
        </div>

        <div ref="chatContainer" class="flex-1 overflow-y-auto p-4 space-y-6 bg-pattern">
          
          <div v-if="isLoading" class="flex justify-center py-10">
            <div class="flex items-center gap-2 px-4 py-2 bg-white rounded-full shadow-sm text-sm text-gray-500">
                <div class="animate-spin rounded-full h-4 w-4 border-2 border-blue-600 border-t-transparent"></div>
                Memuat percakapan...
            </div>
          </div>

          <div v-else v-for="(msg) in messages" :key="msg.id" 
            class="flex flex-col w-full group"
            :class="msg.sender_id === selectedRoom.user_id ? 'items-start' : 'items-end'">
            
            <div class="relative max-w-[75%] md:max-w-[60%] flex gap-2"
                :class="msg.sender_id === selectedRoom.user_id ? 'flex-row' : 'flex-row-reverse'">
                
                <div class="px-5 py-3 shadow-sm text-sm leading-relaxed transition-all duration-200"
                  :class="[
                    msg.sender_id === selectedRoom.user_id 
                      ? 'bg-white text-gray-800 rounded-2xl rounded-tl-none border border-gray-100' 
                      : 'bg-blue-600 text-white rounded-2xl rounded-tr-none shadow-md'
                  ]">
                  <p class="whitespace-pre-wrap">{{ msg.message }}</p>
                  
                  <div class="flex items-center justify-end gap-1 mt-1 select-none"
                    :class="msg.sender_id === selectedRoom.user_id ? 'text-gray-400' : 'text-blue-200'">
                    <span class="text-[10px]">
                        {{ msg.created_at ? new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : 'Just now' }}
                    </span>
                    <Check v-if="msg.sender_id !== selectedRoom.user_id" class="w-3 h-3" />
                  </div>
                </div>

            </div>
          </div>
        </div>

        <div class="p-4 bg-white border-t border-gray-200 z-20">
          <div class="flex items-end gap-2 bg-gray-50 rounded-[24px] px-2 py-2 border border-gray-200 focus-within:border-blue-400 focus-within:ring-4 focus-within:ring-blue-100 transition-all shadow-sm">
            
            <button class="p-3 text-gray-400 hover:text-gray-600 hover:bg-gray-200/50 rounded-full transition-colors self-end">
              <Paperclip class="w-5 h-5" />
            </button>
            
            <textarea 
              id="chatInput"
              v-model="newMessage" 
              @keydown.enter.prevent="sendMessage" 
              rows="1"
              class="flex-1 bg-transparent border-none focus:ring-0 text-gray-700 placeholder:text-gray-400 py-3 px-2 outline-none resize-none max-h-32 custom-scrollbar"
              placeholder="Ketik pesan balasan..." 
              :disabled="isSending"
              style="min-height: 44px;"
            ></textarea>
            
            <button 
              @click="sendMessage" 
              :disabled="!newMessage.trim() || isSending"
              class="bg-blue-600 hover:bg-blue-700 text-white p-3 rounded-full transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-md hover:shadow-lg active:scale-95 transform self-end mb-0.5">
              <Send class="w-5 h-5 ml-0.5" /> </button>
          </div>
          <div class="text-center mt-2">
            <p class="text-[10px] text-gray-400">Tekan Enter untuk mengirim</p>
          </div>
        </div>

      </template>
    </div>
  </div>
</template>

<style scoped>
/* Scrollbar Custom */
.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 20px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: #94a3b8;
}

/* Background Pattern untuk Chat Area */
.bg-pattern {
    background-color: #f0f2f5;
    background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
    background-size: 20px 20px;
}
</style>