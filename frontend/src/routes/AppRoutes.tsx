import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom";

import LoginPage from "../pages/LoginPage";
import RegisterPage from "../pages/RegisterPage";
import DashboardLayout from "../components/DashboardLayout";
import NotesPage from "../pages/NotesPage";
import WelcomePage from "../pages/WelcomePage";
import CreateNotePage from "../pages/CreateNotePage";
import EditNotePage from "../pages/EditNotePage";


export default function AppRoutes() {
    return (
        <BrowserRouter>
            <Routes>
                 <Route
                    path="/"
                    element={<Navigate to="/login" replace />}
                />
                <Route path="/login" element={<LoginPage />} />
                <Route path="/register" element={<RegisterPage />} />
                <Route
                    path="/dashboard"
                    element={<DashboardLayout />}
                >
                    {/* /dashboard */}
                    <Route
                        index
                        element={<WelcomePage />}
                    />

                    {/* /dashboard/notes */}
                    <Route
                        path="notes"
                        element={<NotesPage />}
                    />

                    <Route
                       path="notes/create"
                       element={<CreateNotePage />}
                    />

                    <Route
                        path="notes/:id/edit"
                        element={<EditNotePage />}
                    />

                </Route>
                
                
            </Routes>
        </BrowserRouter>
    );
}