import { createStore } from "vuex";

export default createStore({
  state: {
    userData: {},
    chatroomsData: {},
    selectedChatroomData: {},
    messagesData: [],
    allFriendsData: [],
    paginationForChatrooms: {},
    realtimeData: {},
  },
  getters: {
    getUserData: (state) => state.userData,
    getChatroomsData: (state) => state.chatroomsData,
    getSelectedChatroomData: (state) => state.selectedChatroomData,
    getMessagesData: (state) => state.messagesData,
    getAllFriendsData: (state) => state.allFriendsData,
    getPaginationForChatrooms: (state) => state.paginationForChatrooms,
    getRealtimeData: (state) => state.realtimeData,
  },
  mutations: {
    SET_REALTIME_DATA: (state, value) => {
      state.realtimeData = value;
    },
    SET_USER_DATA: (state, value) => {
      state.userData = value;
    },
    SET_CHATROOMS_DATA: (state, value) => {
      state.chatroomsData = value;
    },
    SET_SELECTED_CHATROOM_DATA: (state, value) => {
      state.selectedChatroomData = value;
    },
    SET_MESSAGES_DATA: (state, value) => {
      state.messagesData = value;
    },
    SET_ALL_FRIENDS_DATA: (state, value) => {
      state.allFriendsData = value;
    },
    SET_PAGINATION_FOR_CHATROOMS: (state, value) => {
      state.paginationForChatrooms = value;
    },
    SET_LOADED_CHATROOMS: (state, value) => {
      state.chatroomsData = [...state.chatroomsData, ...value];
    },
    SET_LOADED_CHATROOMS_AND_MESSAGES: (state, value) => {
      state.messagesData = [...state.messagesData, ...value];
    },
    SET_LOADED_MESSAGES: (state, value) => {
      state.messagesData[value.index].data = [
        ...value.data,
        ...state.messagesData[value.index].data,
      ];
    },
    SET_NEW_MESSAGE: (state, value) => {
      state.messagesData[value.index]?.data.push(value.data);
    },
    INCREASE_MESSAGE_PAGE: (state, value) => {
      state.messagesData[value].currentPage += 1;
    },
    RESET_MESSAGE_PAGE: (state, value) => {
      state.messagesData[value.index].currentPage = 1;
    },
    UPDATE_SEEN: (state, value) => {
      const objs = state.messagesData[value.index].data.filter((item) =>
        value.ids.includes(item.id)
      );
      objs.forEach((obj) => {
        obj.seen = 1;
      });
    },
    UPDATE_SEEN_FROM_CHATROOM: (state, value) => {
      const messagesToBeSeen = state.messagesData[value.index].data.filter(
        (item) => item.seen === 0 && item.user_id !== state.userData.id
      );
      messagesToBeSeen.forEach((element) => {
        element.seen = 1;
      });
    },
    UPDATE_ENTER_FIRST_TIME: (state) => {
      state.selectedChatroomData.enterFirstTime = false;
    },
    SET_USER_ONLINE: (state, userId) => {
      if (state.chatroomsData.length > 0) {
        if (
          state.chatroomsData.find((item) => item.user_id === userId) !==
          undefined
        ) {
          state.chatroomsData.find(
            (item) => item.user_id === userId
          ).partner.online = 1;
        }
      }
      if (state.allFriendsData.length > 0) {
        if (
          state.allFriendsData.find((item) => item.id === userId) !== undefined
        ) {
          state.allFriendsData.find((item) => item.id === userId).online = 1;
        }
      }
    },
    SET_USER_OFFLINE: (state, userId) => {
      if (state.chatroomsData.length > 0) {
        if (
          state.chatroomsData.find((item) => item.user_id === userId) !==
          undefined
        ) {
          state.chatroomsData.find(
            (item) => item.user_id === userId
          ).partner.online = 0;
        }
      }
      if (state.allFriendsData.length > 0) {
        if (
          state.allFriendsData.find((item) => item.id === userId) !== undefined
        ) {
          state.allFriendsData.find((item) => item.id === userId).online = 0;
        }
      }
    },
  },
  actions: {
    setRealtimeData: ({ commit }, value) => {
      commit("SET_REALTIME_DATA", value);
    },
    setUserData: ({ commit }, value) => {
      commit("SET_USER_DATA", value);
    },
    setChatroomsData: ({ commit }, value) => {
      commit("SET_CHATROOMS_DATA", value);
    },
    setSelectedChatroomData: ({ commit }, value) => {
      commit("SET_SELECTED_CHATROOM_DATA", value);
    },
    setMessagesData: ({ commit }, value) => {
      commit("SET_MESSAGES_DATA", value);
    },
    setAllFriendsData: ({ commit }, value) => {
      commit("SET_ALL_FRIENDS_DATA", value);
    },
    setPaginationForChatrooms: ({ commit }, value) => {
      commit("SET_PAGINATION_FOR_CHATROOMS", value);
    },
    addLoadedChatrooms: ({ commit }, value) => {
      commit("SET_LOADED_CHATROOMS", value);
    },
    addLoadedChatroomsAndMessages: ({ commit }, value) => {
      commit("SET_LOADED_CHATROOMS_AND_MESSAGES", value);
    },
    addLoadedMessages: ({ commit }, value) => {
      commit("SET_LOADED_MESSAGES", value);
    },
    addNewMessage: ({ commit }, value) => {
      commit("SET_NEW_MESSAGE", value);
    },
    increaseMessagePage: ({ commit }, value) => {
      commit("INCREASE_MESSAGE_PAGE", value);
    },
    resetMessagePage: ({ commit }, value) => {
      commit("RESET_MESSAGE_PAGE", value);
    },
    updateSeen: ({ commit }, value) => {
      commit("UPDATE_SEEN", value);
    },
    updateSeenFromChatroom: ({ commit }, value) => {
      commit("UPDATE_SEEN_FROM_CHATROOM", value);
    },
    updateEnterFirstTime: ({ commit }, value) => {
      commit("UPDATE_ENTER_FIRST_TIME", value);
    },
    setUserOnline: ({ commit }, value) => {
      commit("SET_USER_ONLINE", value);
    },
    setUserOffline: ({ commit }, value) => {
      commit("SET_USER_OFFLINE", value);
    },
  },
  modules: {},
});
