<template>
    <div v-if="store.getters.getSelectedChatroomData.id"
        class="chat-container hidden text-white sm:block md:block lg:block bg-gray-950 w-7/12 md:w-7/12">

        <!-- Top Bar -->
        <div class="mb-5 py-4 sticky top-0 z-50 bg-black px-5 flex justify-between items-center">
            <div class=" flex w-1/2 items-center">
                <img class="h-14 rounded-full inline-block mr-2"
                    :src="store.getters.getSelectedChatroomData.partner.profileUrl">
                <span class=" text-cyan-300 font-bold text-2xl ml-5">{{
                    store.getters.getSelectedChatroomData.partner.name
                }}</span>
            </div>
            <div class="w-1/2 text-right text-green-400 font-bold">Online</div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class='flex space-x-2 justify-center items-center h-10 dark:invert'>
            <span class='sr-only'>Loading...</span>
            <div class='h-4 w-4 bg-red-600 rounded-full animate-bounce [animation-delay:-0.3s]'></div>
            <div class='h-4 w-4 bg-red-600 rounded-full animate-bounce [animation-delay:-0.15s]'></div>
            <div class='h-4 w-4 bg-red-600 rounded-full animate-bounce'></div>
        </div>

        <!-- Message  -->
        <div @scroll="handleScroll" v-if="currentRoomMessages.data.length > 0" class="messages" ref="chatroom">
            <div v-for="message in currentRoomMessages?.data" :key="message.id" class="pl-5 text-balance">

                <div class="text-left my-4" :class="message.content !== '' ? '' : 'flex'"
                    v-if="message.user_id !== store.getters.getUserData.id">
                    <div class="inline-block relative z-0 h-full">
                        <div v-if="
                            // store.getters.getSelectedChatroomData.partner.online
                            true
                        " style="background-color: #00ff12;"
                            class="absolute border-4 border-black w-5 h-5 rounded-full right-0 bottom-0">
                        </div>
                        <img class=" w-16 rounded-full inline-block mr-2"
                            :src="store.getters.getSelectedChatroomData.partner.profileUrl">
                    </div>
                    <span v-if="message.content !== ''" :class="{ 'w-2/3': message.content.split(' ').length > 15 }"
                        class=" text-lg text-wrap text-justify inline-block bg-blue-500 pr-5 pl-4 py-1 rounded-tl-xl rounded-br-xl rounded-tr-xl">{{
                            message.content }}</span>
                    <img loading="lazy" @load="scrollToBottomAfterImageLoaded" v-else
                        class=" object-fill w-60 rounded-lg" :src="message.image">
                </div>
                <div v-else class=" relative text-right my-4 flex justify-end items-end">
                    <span v-if="message.content !== ''" :class="{ 'w-2/3': message.content.split(' ').length > 15 }"
                        class=" text-lg text-wrap text-justify inline-block bg-blue-500 pl-4 pr-12 py-1 rounded-tr-xl rounded-bl-xl rounded-tl-xl">{{
                            message.content }}</span>
                    <div v-else>
                        <img loading="lazy"
                            v-if="!sendingImage || currentRoomMessages.data[currentRoomMessages.data.length - 1].id !== message.id"
                            @load="scrollToBottomAfterImageLoaded" class="object-fill w-60 rounded-lg"
                            :src="message.image">
                        <div v-else
                            class="text-lg text-wrap text-justify inline-block bg-blue-500 pl-4 pr-12 py-1 rounded-tr-xl rounded-bl-xl rounded-tl-xl">
                            Sending image ...</div>
                    </div>
                    <i v-if="message.seen" class="fa-solid fa-lg fa-check absolute bottom-4 right-2"></i>
                    <i v-if="message.seen" class="fa-solid fa-lg fa-check absolute bottom-4 right-4"></i>
                    <i v-else-if="message.hasOwnProperty('image')"
                        class="fa-solid fa-lg fa-check absolute bottom-4 right-2"></i>
                    <i v-else class="fa-solid fa-clock text-yellow-400 absolute bottom-2 right-2"></i>
                </div>

            </div>
        </div>
        <div v-else
            class="flex messages justify-center items-center w-1/3 border-2 m-auto my-44 px-2 py-4 border-white rounded-2xl">
            <div class=" text-center">
                <i style="transform: scaleX(-1)" class="fa-regular fa-comment-dots mb-5 text-6xl text-white"></i>
                <h1 class="text-2xl font-bold mb-5">No Message Yet !</h1>
                <h1 class="text-md text-neutral-500">No message in your chatroom yet. Start chatting with your friend {{
                    store.getters.getSelectedChatroomData.partner.name }}.
                </h1>
            </div>
        </div>

        <!-- Typing status -->
        <!-- <div v-if="showTyping" class="">
            {{ store.getters.getSelectedChatroomData.partner.name }} is typing ...</div> -->

        <!-- Input field -->
        <div class="input-container flex">
            <input @focusin="sendEventFriendIsTyping" @focusout="sendEventFriendStopTyping"
                class=" rounded-s-xl bg-blue-950 w-full" @keyup.enter="handleSendMessage" ref="sendMessage" type="text"
                placeholder="Type your message here ...">
            <input ref="inputFile" @change="handleSendImage" type="file" class="hidden">
            <i @click="openFileImage"
                class="fa-solid fa-paperclip fa-image pr-4 fa-xl hover:cursor-pointer hover:text-blue-500 text-white bg-blue-950 flex justify-center items-center"></i>
            <i @click="handleSendMessage"
                class="fa-solid fa-paper-plane fa-xl hover:cursor-pointer hover:text-blue-500 text-white bg-blue-950 flex justify-center items-center pr-3 rounded-e-xl"></i>

        </div>
    </div>
    <div v-else class="sm:block md:block lg:block bg-gray-950 w-7/12 md:w-7/12 ">
        <div class="flex justify-center items-center h-full">
            <div class="text-slate-600 text-center text-xl">Choose a chatroom to chat
                with your
                friend.
            </div>
        </div>
    </div>
</template>

<script>
import { config } from '@/config';
import { ref, onUpdated, watchEffect, onMounted } from 'vue';
import { useStore } from 'vuex';

export default {
    setup() {
        const store = useStore()
        const token = localStorage.getItem("token")
        const sendingMessage = ref(false)
        const sendMessage = ref('')
        const inputFile = ref(null)
        const sendingImage = ref(false)
        const imagesLoaded = ref(false)

        let index
        let scrollBot = true

        // Broadcasting Send Message
        onMounted(() => {
            var pusher = window.pusher;
            var channel = pusher.subscribe(`chatroom`);
            channel.bind('sendMessage', async function (data) {
                const { message } = data
                if (message.image !== null && !message.image.includes('htt')) {
                    message.image = `${config.chatroomImages}${message.image}`
                }

                if (message.user_id !== store.getters.getUserData.id) {
                    if (currentRoomMessages.value) {
                        const shouldUpdateChatroom = store.getters.getMessagesData.find(item => item.chatroom_id === message.chatroom_id)
                        const obj = { index: store.getters.getMessagesData.indexOf(shouldUpdateChatroom), data: message }
                        await store.dispatch('addNewMessage', obj)

                        // if (message.chatroom_id === store.getters.getSelectedChatroomData.chatroom_id) {
                        // add to cuurent Messages
                        // const pushNewMessage = await currentRoomMessages.value.data.find(item => item.id === message.id)
                        // if (!pushNewMessage && message.user_id !== store.getters.getUserData.id) {
                        //     currentRoomMessages.value.data.push(message)
                        // }

                        // else if (store.getters.getChatroomsData.find(item => item.chatroom_id === message.chatroom_id) !== undefined) {
                        //     // find chatroom and add real time send message
                        //     const shouldUpdateChatroom = store.getters.getMessagesData.find(item => item.chatroom_id === message.chatroom_id)
                        //     if (shouldUpdateChatroom) {
                        //         const obj = { index: store.getters.getMessagesData.indexOf(shouldUpdateChatroom), data: message }
                        //         await store.dispatch('addNewMessage', obj)
                        //     }
                        // }
                    }
                    //  else {
                    //     // find chatroom and add real time send message
                    //     const shouldUpdateChatroom = store.getters.getMessagesData.find(item => item.chatroom_id === message.chatroom_id)
                    //     if (shouldUpdateChatroom) {
                    //         const obj = { index: store.getters.getMessagesData.indexOf(shouldUpdateChatroom), data: message }
                    //         await store.dispatch('addNewMessage', obj)
                    //     }
                    // }
                }
            });

            // Typing
            // channel.bind('typing', async function (data) {
            //     console.log(data);
            // })
        })

        // Broadcasting new chatroom
        onMounted(() => {
            var pusher = window.pusher;
            var channel = pusher.subscribe(`chatroom`);
            channel.bind('newChatroom', async function (data) {
                const { newChatroomData } = data
                console.log(newChatroomData);
                const { createrId, chatrooms, messages, newChatroom, paginationForChatrooms } = newChatroomData
                if (chatrooms.find(item => item.chatroom_id === newChatroom.id).user_id === store.getters.getUserData.id) {
                    const newChatroomToBeUpdated = chatrooms.find(item => item.chatroom_id === newChatroom.id)
                    newChatroomToBeUpdated.partner = store.getters.getAllFriendsData.find(item => item.id === createrId)
                    const index = chatrooms.indexOf(newChatroomToBeUpdated)
                    chatrooms[index] = newChatroomToBeUpdated

                    newChatroom.pivot.user_id = store.getters.getUserData.id

                    await chatrooms.map(chatroom => {
                        if (chatroom.partner.id !== newChatroomToBeUpdated.partner.id) {
                            const friendProfileUrl = `${config.profileImageUrl}${chatroom.partner.profileUrl}`
                            chatroom.partner.profileUrl = friendProfileUrl
                        }
                    })
                    await store.dispatch('setChatroomsData', chatrooms)
                    await store.dispatch('setMessagesData', messages)
                    await store.dispatch('setPaginationForChatrooms', paginationForChatrooms)
                }
            })
        })

        // // Broadcasting typing
        // const showTyping = ref(false)
        // onMounted(() => {
        //     var pusher = window.pusher
        //     var channel = pusher.subscribe('chatroom')
        //     channel.bind('typing', async function (data) {
        //         const { info } = data
        //         const chatroomId = info.chatroomId
        //         const typingUserId = info.typingUserId

        //         if (store.getters.getSelectedChatroomData.chatroom_id === chatroomId && typingUserId !== store.getters.getUserData.id) {
        //             showTyping.value = true
        //         }
        //     })
        // })
        // onMounted(() => {
        //     var pusher = window.pusher
        //     var channel = pusher.subscribe('chatroom')
        //     channel.bind('stopTyping', async function (data) {
        //         const { info } = data
        //         const chatroomId = info.chatroomId
        //         const typingUserId = info.typingUserId

        //         if (store.getters.getSelectedChatroomData.chatroom_id === chatroomId && typingUserId !== store.getters.getUserData.id) {
        //             showTyping.value = false
        //         }
        //     })
        // })

        // // Real time typing message
        // const sendEventFriendIsTyping = async () => {
        //     await fetch(`${config.apiUrl}sendEventFriendIsTyping`, {
        //         method: 'POST',
        //         headers: {
        //             'Authorization': `Bearer ${token}`,
        //             'Content-Type': 'application/json' // Specify the content type if needed
        //         },
        //         body: JSON.stringify({ chatroomId: store.getters.getSelectedChatroomData.chatroom_id, typingUserId: store.getters.getUserData.id })
        //     })
        // }
        // const sendEventFriendStopTyping = async () => {
        //     await fetch(`${config.apiUrl}sendEventFriendStopTyping`, {
        //         method: 'POST',
        //         headers: {
        //             'Authorization': `Bearer ${token}`,
        //             'Content-Type': 'application/json' // Specify the content type if needed
        //         },
        //         body: JSON.stringify({ chatroomId: store.getters.getSelectedChatroomData.chatroom_id, typingUserId: store.getters.getUserData.id })
        //     })
        // }

        // ---------------------------------------------------

        // Send Image 
        const openFileImage = () => {
            inputFile.value.click()
        }
        const handleSendImage = async (event) => {
            const newMessageFirst = {
                id: currentRoomMessages.value.data[currentRoomMessages.value.data.length - 1] ? currentRoomMessages.value.data[currentRoomMessages.value.data.length - 1].id + 1 : 1,
                content: '',
                user_id: store.getters.getUserData.id,
                chatroom_id: store.getters.getSelectedChatroomData.chatroom_id
            }
            currentRoomMessages.value.data.push(newMessageFirst)

            const file = event.target.files[0];
            // Cant be sent as json 
            const formData = new FormData();
            formData.append('image', file);
            formData.append('userId', store.getters.getUserData.id);
            formData.append('chatroomId', store.getters.getSelectedChatroomData.chatroom_id);

            sendingImage.value = true
            const response = await fetch(`${config.apiUrl}sendImage`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`, // Specify the content type if needed
                },
                body: formData
            })
            const fileName = await response.json()
            const image = `${config.chatroomImages}${fileName}`
            currentRoomMessages.value.data[currentRoomMessages.value.data.length - 1].image = image

            // const newMessage = {
            //     id: currentRoomMessages.value.data[currentRoomMessages.value.data.length - 1] ? currentRoomMessages.value.data[currentRoomMessages.value.data.length - 1].id + 1 : 1,
            //     content: '',
            //     user_id: store.getters.getUserData.id,
            //     image,
            //     chatroom_id: store.getters.getSelectedChatroomData.chatroom_id
            // }
            // currentRoomMessages.value.data.push(newMessage)

            sendingImage.value = false
            inputFile.value.value = '';
        }

        // Auto scroll to last message
        const scrollToBottomAfterImageLoaded = () => {
            if (scrollBot && chatroom.value) {
                chatroom.value.scrollTop = chatroom.value.scrollHeight
            }
        }

        // Send Message
        const handleSendMessage = async () => {
            if (sendMessage.value.value !== '') {
                const message = sendMessage.value.value
                sendMessage.value.value = ''
                const userId = store.getters.getUserData.id
                const chatroomId = store.getters.getSelectedChatroomData.chatroom_id

                const newMessage = { id: currentRoomMessages.value.data[currentRoomMessages.value.data.length - 1] ? currentRoomMessages.value.data[currentRoomMessages.value.data.length - 1].id + 1 : 1, content: message, user_id: userId, chatroom_id: chatroomId, image: null }
                await currentRoomMessages.value.data.push(newMessage)

                sendingMessage.value = true

                const response = await fetch(`${config.apiUrl}sendMessage`, {
                    method: 'POST',
                    headers: { Authorization: `Bearer ${token}`, "Content-Type": 'application/json' },
                    body: JSON.stringify(newMessage)
                })
                const dataFromServer = await response.json()
                const { newMessageFromServer } = dataFromServer

                if (newMessageFromServer) {
                    sendingMessage.value = false
                }

            }
        }

        const chatroom = ref(null)
        onUpdated(async () => {
            if (localStorage.getItem('token') && scrollBot) {
                if (scrollBot && chatroom.value) {
                    chatroom.value.scrollTop = chatroom.value.scrollHeight

                    // Broadcast seen and update db 
                    if (currentRoomMessages.value) {
                        const unseenMessagesArray = currentRoomMessages.value?.data.filter(item => item.seen === 0 && item.user_id !== store.getters.getUserData.id)
                        const unseenMessagesIds = unseenMessagesArray.map(item => item.id)
                        if (unseenMessagesArray.length > 0) {
                            await fetch(`${config.apiUrl}updateSeen`, {
                                method: 'POST',
                                headers: { Authorization: `Bearer ${token}`, 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    ids: unseenMessagesIds,
                                    data: unseenMessagesArray
                                })
                            })
                        }
                    }
                }
            }
        })

        // real time seen
        onMounted(() => {
            var pusher = window.pusher;
            var channel = pusher.subscribe(`chatroom`);
            channel.bind('sendSeen', async function (data) {

                const { seen } = data
                const chatroomId = seen[0].chatroom_id
                const messageIds = seen.map(item => item.id)
                const exist = store.getters.getChatroomsData.find(item => item.chatroom_id === chatroomId)

                const messageData = store.getters.getMessagesData.find(item => item.chatroom_id === chatroomId)
                const index = store.getters.getMessagesData.indexOf(messageData)

                if (exist) {
                    await store.dispatch('updateSeen', {
                        index: index,
                        ids: messageIds
                    })
                }
            });
        })

        // Filtering messages
        const currentRoomMessages = ref([])
        watchEffect(() => {
            const id = store.getters.getSelectedChatroomData.chatroom_id;
            const messageData = store.getters.getMessagesData.find(item => item.chatroom_id === id)
            currentRoomMessages.value = messageData
            index = store.getters.getMessagesData.indexOf(messageData)
        })

        // load more message
        let pagination = null
        const loading = ref(false)
        watchEffect(() => {
            if (currentRoomMessages.value) {
                pagination = {
                    currentPage: currentRoomMessages.value.currentPage,
                    lastPage: currentRoomMessages.value.lastPage
                }
            }
        })
        const loadMore = async () => {
            const keepLoading = pagination.currentPage < pagination.lastPage

            if (keepLoading) {
                scrollBot = false
                loading.value = true
                await store.dispatch('increaseMessagePage', index)

                const response = await fetch(`${config.apiUrl}loadMessages?page=${pagination.currentPage}&id=${store.getters.getSelectedChatroomData.chatroom_id}`, {
                    method: 'GET',
                    headers: { Authorization: `Bearer ${token}` },
                })
                const dataFromServer = await response.json()
                const { loadedMessages } = dataFromServer

                if (loadedMessages) {
                    loadedMessages.map(message => {
                        if (message.content === '') {
                            message.image = `${config.chatroomImages}${message.image}`
                        }
                    })
                    await store.dispatch("addLoadedMessages", {
                        index: index,
                        data: loadedMessages
                    })
                }
                loading.value = false
                setTimeout(() => {
                    scrollBot = true
                }, 1000)
            }

        }
        const handleScroll = async (event) => {
            if (event.target.scrollTop < 5 && event.target.scrollTop !== 0) {
                loadMore()
            }
        };

        return {
            store: store, config, chatroom, currentRoomMessages, handleScroll, loading, sendMessage,
            // sendEventFriendIsTyping, sendEventFriendStopTyping, showTyping, 
            scrollToBottomAfterImageLoaded, imagesLoaded, scrollBot, handleSendMessage, sendingMessage, sendingImage, inputFile, openFileImage, handleSendImage
        }
    }
}

</script>

<style scoped>
/* Add scoped to limit the style to this component only */

/* Remove the border and outline when the input is focused */
input:focus {
    border: none;
    outline: none;
}

.chat-container {
    display: flex;
    flex-direction: column;
    height: 100vh;
}

.messages {
    flex: 1;
    overflow-y: auto;
    padding: 10px;
}

.message {
    margin-bottom: 10px;
}

.input-container {
    position: sticky;
    bottom: 0;
    background: #000000;
    padding: 15px;
    box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
}

input {
    padding: 10px 15px;
}
</style>