import { createRouter, createWebHistory } from 'vue-router';
import HomeView from '../views/HomeView.vue';
import RoomListView from '../views/RoomListView.vue';
import RoomDetailView from '../views/RoomDetailView.vue';
import BookingView from '../views/BookingView.vue';
import AboutView from '../views/AboutView.vue';
import ContactView from '../views/ContactView.vue';
import AdminLayout from '../views/admin/AdminLayout.vue';
import AdminDashboard from '../views/admin/DashboardView.vue';
import AdminReservations from '../views/admin/ReservationsView.vue';
import AdminRooms from '../views/admin/AdminRoomsView.vue';
import AdminGuests from '../views/admin/GuestsView.vue';
import AdminPayments from '../views/admin/PaymentsView.vue';
import AdminSettings from '../views/admin/SettingsView.vue';
import AdminAmenities from '../views/admin/AmenitiesView.vue';
import AdminMessages from '../views/admin/MessagesView.vue';
import AdminChatbot from '../views/admin/ChatbotView.vue';
import LoginView from '../views/auth/LoginView.vue';
import RegisterView from '../views/auth/RegisterView.vue';
import ProfileView from '../views/ProfileView.vue';
import MyBookingsView from '../views/MyBookingsView.vue';
import ForgotPasswordView from '../views/auth/ForgotPasswordView.vue';
import ResetPasswordView from '../views/auth/ResetPasswordView.vue';
import NotificationsView from '../views/NotificationsView.vue';
import AdminLoginView from '../views/admin/AdminLoginView.vue';

const routes = [
    {
        path: '/',
        name: 'home',
        component: HomeView
    },
    {
        path: '/forgot-password',
        name: 'forgot-password',
        component: ForgotPasswordView
    },
    {
        path: '/reset-password/:token',
        name: 'reset-password',
        component: ResetPasswordView
    },
    {
        path: '/profile',
        name: 'profile',
        component: ProfileView
    },
    {
        path: '/my-bookings',
        name: 'my-bookings',
        component: MyBookingsView
    },
    {
        path: '/notifications',
        name: 'notifications',
        component: NotificationsView
    },
    {
        path: '/login',
        name: 'login',
        component: LoginView
    },
    {
        path: '/register',
        name: 'register',
        component: RegisterView
    },
    {
        path: '/about',
        name: 'about',
        component: AboutView
    },
    {
        path: '/contact',
        name: 'contact',
        component: ContactView
    },
    {
        path: '/rooms',
        name: 'rooms',
        component: RoomListView
    },
    {
        path: '/rooms/:id',
        name: 'room-detail',
        component: RoomDetailView,
        props: true
    },
    {
        path: '/book-now',
        name: 'booking',
        component: BookingView
    },
    {
        path: '/booking/success',
        name: 'booking-success',
        component: () => import('../views/BookingSuccessView.vue')
    },
    {
        path: '/booking/cancel',
        name: 'booking-cancel',
        component: () => import('../views/BookingCancelView.vue')
    },
    {
        path: '/admin/login',
        name: 'admin-login',
        component: AdminLoginView
    },
    // Admin Routes
    {
        path: '/admin',
        component: AdminLayout,
        meta: { requiresAdmin: true },
        children: [
            { path: '', name: 'admin-dashboard', component: AdminDashboard },
            { path: 'reservations', name: 'admin-reservations', component: AdminReservations },
            { path: 'rooms', name: 'admin-rooms', component: AdminRooms },
            { path: 'guests', name: 'admin-guests', component: AdminGuests },
            { path: 'payments', name: 'admin-payments', component: AdminPayments },
            { path: 'settings', name: 'admin-settings', component: AdminSettings },
            { path: 'amenities', name: 'admin-amenities', component: AdminAmenities },
            { path: 'messages', name: 'admin-messages', component: AdminMessages },
            { path: 'chatbot', name: 'admin-chatbot', component: AdminChatbot },
        ]
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

router.beforeEach((to, from, next) => {
    const userJson = localStorage.getItem('user');
    const user = userJson ? JSON.parse(userJson) : null;
    const isAuthenticated = !!localStorage.getItem('token');

    // Protect Admin Routes
    if (to.path.startsWith('/admin') && to.name !== 'admin-login') {
        if (!isAuthenticated) {
            return next({ name: 'admin-login' });
        }
        if (user && user.role !== 'admin') {
            return next({ name: 'rooms' }); // Redirect regular users to rooms
        }
    }

    // Protect Member Routes
    const memberRoutes = ['profile', 'my-bookings', 'notifications', 'booking'];
    if (memberRoutes.includes(to.name) && !isAuthenticated) {
        return next({ name: 'login' });
    }

    // Redirect authenticated users away from login pages
    if ((to.name === 'login' || to.name === 'admin-login') && isAuthenticated) {
        if (user && user.role === 'admin') {
            return next({ name: 'admin-dashboard' });
        }
        return next({ name: 'home' });
    }

    next();
});

export default router;
