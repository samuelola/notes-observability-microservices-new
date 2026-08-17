
import api from "./client";

export interface Note {
    id: number;
    title: string;
    content: string;
    user_id: number;
    image_path?: string | null;
    image_url: string | null;
    created_at?: string;
    updated_at?: string;
}

export interface CreateNoteData {
    title: string;
    content: string;
    image?: File | null;
}

export interface UpdateNoteData {
    title: string;
    content: string;
    image?: File | null;
}

export interface Pagination {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
    from: number | null;
    to: number | null;
    has_more_pages: boolean;
}


export interface NotesResponse {
    data: Note[];
    pagination: Pagination;
}

export async function getNotes(page: number = 1) {
    const response = await api.get("/notes", {
        params: {
            page,
        },
    });

    // console.log("FULL API RESPONSE:", response.data);

    return response.data as NotesResponse;
}


export async function createNote(
    data: CreateNoteData
) {
    const formData = new FormData();

    formData.append("title", data.title);
    formData.append("content", data.content);

    if (data.image) {
        formData.append("image", data.image);
    }

    const response = await api.post(
        "/notes",
        formData
    );

    return response.data;
}


export const updateNote = async (
    id: number,
    data: UpdateNoteData
) => {
    const formData = new FormData();

    formData.append("title", data.title);
    formData.append("content", data.content);

    if (data.image) {
        formData.append("image", data.image);
    }

    formData.append("_method", "PUT");

    const response = await api.post(
        `/notes/${id}`,
        formData
    );

    return response.data;
};


export const getNote = async (id: number) => {
    const response = await api.get(
        `/notes/${id}`
    );

    return response.data;
};

export const deleteNote = async (id: number) => {
    const response = await api.delete(
        `/notes/${id}`
    );

    return response.data;
};