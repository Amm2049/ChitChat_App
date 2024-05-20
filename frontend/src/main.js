import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";
import store from "./store";
import "../src/style.css";

createApp(App).use(store).use(router).mount("#app");

import Echo from "laravel-echo";

import Pusher from "pusher-js";
window.Pusher = Pusher;

window.Echo = new Echo({
  broadcaster: "pusher",
  key: process.env.VITE_PUSHER_APP_KEY,
  cluster: process.env.VITE_PUSHER_APP_CLUSTER,
  forceTLS: true,
});
