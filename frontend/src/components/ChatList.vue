<template>
    <div @scroll="handleScroll"
        class="bg-black h-screen overflow-y-scroll text-white w-full sm:w/4/12 md:w-4/12 lg:w-4/12 px-2 md:px-2 lg:px-3">
        <!-- Title & Search -->
        <div class="sticky top-0 bg-black pb-3 z-50">
            <div class="mb-5 flex justify-between items-center sm:mb-3 md:mb-4 lg:mb-5 pt-4">
                <h1 class=" text-4xl sm:text-4xl md:text-4xl font-bold">Chats</h1>
                <h6 v-if="store.getters.getUserData" class="mt-2 text-xl text-cyan-400">{{
                    store.getters.getUserData.email }}</h6>
            </div>
            <div class="my-6 sm:my-4 md:my-5">
                <input class=" bg-slate-800 px-5 py-2 w-full rounded-3xl" type="text"
                    placeholder="Search your conversations ...">
            </div>
        </div>

        <!-- Chats  -->
        <div class=" z-0" v-if="store.getters.getChatroomsData.length > 0">
            <div @click="() => {
                selectChatroom(chatroom.id)
            }" class="p-3 flex hover:bg-gray-950 hover:cursor-pointer"
                :class="store.getters.getSelectedChatroomData.id === chatroom.id ? 'bg-slate-900 hover:bg-slate-900' : ''"
                v-for="chatroom in store.getters.getChatroomsData" :key="chatroom.id">
                <div class=" w-1/6 relative">
                    <img class="rounded-full" :src="chatroom.partner.profileUrl">
                    <div v-if="chatroom.partner.online" style="background-color: #00ff12;"
                        class="absolute border-4 border-black w-6 h-6 rounded-full right-0 bottom-0">
                    </div>
                </div>
                <div class="w-4/5 ml-5 flex flex-col justify-around">
                    <div class=" flex justify-between">
                        <h1 class=" font-bold text-cyan-400 text-xl">{{ chatroom.chatroomName === null ?
                            chatroom.partner.name :
                            chatroomName }}
                        </h1>

                        <h1 v-if="store.getters.getMessagesData.find(item => item.chatroom_id ===
                            chatroom.chatroom_id) && store.getters.getMessagesData.find(item => item.chatroom_id ===
                                chatroom.chatroom_id).data.filter(item => item.seen === 0 && item.user_id !==
                                    store.getters.getUserData.id).length > 0"
                            class=" text-white rounded-xl  px-2 py-1 bg-red-600">
                            {{ store.getters.getMessagesData.find(item => item.chatroom_id ===
                                chatroom.chatroom_id).data.filter(item => item.seen === 0 && item.user_id !==
                                    store.getters.getUserData.id).length }} new messages
                        </h1>
                    </div>
                    <div>You: send a sticker</div>
                </div>
            </div>
        </div>
        <div v-else class=" mt-44 w-1/2 m-auto border-2 rounded-xl p-3">
            <div class="flex justify-center mb-5">
                <i @click="router.push({ name: 'friends' })"
                    class="fa-solid fa-hippo fa-4x hover:text-cyan-300 cursor-pointer"></i>
            </div>
            <div class=" text-cyan-600 text-center">
                There is no chatroom yet! Let's create spaces with your friends.
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class='flex space-x-2 justify-center items-center bg-white h-10 dark:invert'>
            <span class='sr-only'>Loading...</span>
            <div class='h-4 w-4 bg-red-600 rounded-full animate-bounce [animation-delay:-0.3s]'></div>
            <div class='h-4 w-4 bg-red-600 rounded-full animate-bounce [animation-delay:-0.15s]'></div>
            <div class='h-4 w-4 bg-red-600 rounded-full animate-bounce'></div>
        </div>

    </div>
</template>

<script>

import { config } from '@/config'
import { ref, watchEffect } from 'vue'
import { useRouter } from 'vue-router'
import { useStore } from 'vuex'

export default {
    setup() {
        const store = useStore()
        const token = localStorage.getItem('token')
        const router = useRouter()

        // Select chatroom
        const selectChatroom = async (chatroomId) => {

            const chatrooms = store.getters.getChatroomsData
            const selectChatroomData = chatrooms.find(item => item.id === chatroomId)

            await store.dispatch("setSelectedChatroomData", selectChatroomData)


            // Update messages seen
            const chatroomIdForSeen = store.getters.getSelectedChatroomData.chatroom_id
            const exist = store.getters.getChatroomsData.find(item => item.chatroom_id === chatroomIdForSeen)

            const messageData = store.getters.getMessagesData.find(item => item.chatroom_id === chatroomIdForSeen)
            const index = store.getters.getMessagesData.indexOf(messageData)

            if (exist) {
                await store.dispatch('updateSeenFromChatroom', {
                    index: index,
                })
            }
        }

        // Load more chatrooms
        let pagination = null
        const loading = ref(0)

        watchEffect(() => {
            pagination = store.getters.getPaginationForChatrooms
        })

        const loadMore = async () => {
            const keepLoading = pagination.currentPage < pagination.lastPage
            if (keepLoading) {
                loading.value = true
                pagination.currentPage += 1
                const response = await fetch(`${config.apiUrl}loadChatrooms?page=${pagination.currentPage}`, {
                    method: 'GET',
                    headers: { Authorization: `Bearer ${token}` },
                })
                const dataFromServer = await response.json()
                const { loadedChatrooms, loadedMessages } = dataFromServer

                if (loadedChatrooms) {
                    await loadedChatrooms.map(chatroom => {
                        const friendProfileUrl = `${config.profileImageUrl}${chatroom.partner.profileUrl}`
                        chatroom.partner.profileUrl = friendProfileUrl
                    })
                    await store.dispatch("addLoadedChatrooms", loadedChatrooms)
                    await store.dispatch("addLoadedChatroomsAndMessages", loadedMessages)
                }
                loading.value = false
            }
            loading.value = false
        }

        const handleScroll = (event) => {
            if (event.target.scrollTop + event.target.clientHeight >= event.target.scrollHeight - 10) {
                loadMore()
            }
        };

        return { store, selectChatroom, loadMore, loading, handleScroll, router }
    }
}
</script>
