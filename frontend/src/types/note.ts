export interface Note {
    id: number;
    user_id: number;
    title: string;
    content: string;
    image_path: string | null;
    created_at: string;
    updated_at?: string;
}