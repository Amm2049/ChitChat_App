import { createRouter, createWebHistory } from "vue-router";
import HomePage from "@/components/HomePage.vue";
import LoginPage from "@/components/LoginPage.vue";
import RegisterPage from "@/components/RegisterPage.vue";
import FriendsPage from "@/pages/FriendsPage.vue";
import ProfilePage from "@/pages/ProfilePage.vue";

const requireAuth = (to, from, next) => {
  const token = localStorage.getItem("token");
  if (token) {
    next();
  } else {
    next("/login");
  }
};

const noRequireAuth = (to, from, next) => {
  const token = localStorage.getItem("token");
  if (!token) {
    next();
  } else {
    next("/");
  }
};

const routes = [
  {
    path: "/",
    name: "home",
    component: HomePage,
    beforeEnter: requireAuth,
  },
  {
    path: "/login",
    name: "login",
    component: LoginPage,
    beforeEnter: noRequireAuth,
  },
  {
    path: "/register",
    name: "register",
    component: RegisterPage,
    beforeEnter: noRequireAuth,
  },
  {
    path: "/friends",
    name: "friends",
    component: FriendsPage,
    beforeEnter: requireAuth,
  },
  {
    path: "/profile",
    name: "profile",
    component: ProfilePage,
    beforeEnter: requireAuth,
  },
];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
});

export default router;
