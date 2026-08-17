import api from "./client";

export interface LoginPayload {
    email: string;
    password: string;
}

export interface RegisterPayload {
    name: string;
    email: string;
    password: string;
}

export const login = async (data: LoginPayload) => {
    const response = await api.post("/auth/login", data);

    return response.data;
};

export const register = async (data: RegisterPayload) => {
    const response = await api.post("/auth/register", data);
    return response.data;
};

export async function logout() {
    const response = await api.post("/auth/logout");

    return response.data;
}