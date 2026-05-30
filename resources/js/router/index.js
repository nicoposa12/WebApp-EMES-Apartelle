import { createRouter, createWebHistory } from 'vue-router';

// ── Eagerly loaded: Landing page (fastest first paint) ──
import HomeView from '../views/HomeView.vue';

// ── Lazy-loaded: All other routes (on-demand code splitting) ──
const RoomListView = () => import('../views/RoomListView.vue');
const RoomDetailView = () => import('../views/RoomDetailView.vue');
const BookingView = () => import('../views/BookingView.vue');
const AboutView = () => import('../views/AboutView.vue');
const ContactView = () => import('../views/ContactView.vue');
const LoginView = () => import('../views/auth/LoginView.vue');
const RegisterView = () => import('../views/auth/RegisterView.vue');
const ProfileView = () => import('../views/ProfileView.vue');
const MyBookingsView = () => import('../views/MyBookingsView.vue');
const ForgotPasswordView = () => import('../views/auth/ForgotPasswordView.vue');
const ResetPasswordView = () => import('../views/auth/ResetPasswordView.vue');
const NotificationsView = () => import('../views/NotificationsView.vue');
const AdminLoginView = () => import('../views/admin/AdminLoginView.vue');

// ── Admin views (only loaded when admin navigates to them) ──
const AdminLayout = () => import('../views/admin/AdminLayout.vue');
const AdminDashboard = () => import('../views/admin/DashboardView.vue');
const AdminReservations = () => import('../views/admin/ReservationsView.vue');
const AdminRooms = () => import('../views/admin/AdminRoomsView.vue');
const AdminGuests = () => import('../views/admin/GuestsView.vue');
const AdminPayments = () => import('../views/admin/PaymentsView.vue');
const AdminSettings = () => import('../views/admin/SettingsView.vue');
const AdminAmenities = () => import('../views/admin/AmenitiesView.vue');
const AdminMessages = () => import('../views/admin/MessagesView.vue');
const AdminChatbot = () => import('../views/admin/ChatbotView.vue');
const AdminReports = () => import('../views/admin/ReportsView.vue');
const AdminDisputes = () => import('../views/admin/DisputesView.vue');

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
            { path: 'reports', name: 'admin-reports', component: AdminReports },
            { path: 'settings', name: 'admin-settings', component: AdminSettings },
            { path: 'amenities', name: 'admin-amenities', component: AdminAmenities },
            { path: 'messages', name: 'admin-messages', component: AdminMessages },
            { path: 'chatbot', name: 'admin-chatbot', component: AdminChatbot },
            { path: 'disputes', name: 'admin-disputes', component: AdminDisputes },
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
        if (user && !['admin', 'staff'].includes(user.role)) {
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
