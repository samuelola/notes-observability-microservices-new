import { useEffect, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";

import {
    getNote,
    updateNote,
} from "../api/notesApi";

import "../css/EditNotePage.css";

export default function EditNotePage() {
    const { id } = useParams();
    const navigate = useNavigate();

    const [title, setTitle] = useState("");
    const [content, setContent] = useState("");

    const [image, setImage] = useState<File | null>(null);
    const [currentImage, setCurrentImage] = useState<string | null>(null);

    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState("");

    useEffect(() => {
        const fetchNote = async () => {
            try {
                setLoading(true);
                setError("");

                if (!id) {
                    throw new Error("Note ID is missing");
                }

                const response = await getNote(Number(id));

                const note = response.data;

                setTitle(note.title);
                setContent(note.content);
                setCurrentImage(note.image_url ?? null);

            } catch (error) {
                console.error(error);

                setError(
                    "Unable to load this note."
                );
            } finally {
                setLoading(false);
            }
        };

        fetchNote();
    }, [id]);

    const handleSubmit = async (
        e: React.FormEvent
    ) => {
        e.preventDefault();

        if (!id) {
            return;
        }

        try {
            setSaving(true);
            setError("");

            await updateNote(Number(id), {
                title,
                content,
                image,
            });

            navigate("/dashboard/notes");

        } catch (error) {
            console.error(error);

            setError(
                "Unable to update the note."
            );
        } finally {
            setSaving(false);
        }
    };

    if (loading) {
        return (
            <div className="edit-note-loading">
                <div className="loader"></div>

                <p>
                    Loading note...
                </p>
            </div>
        );
    }

    return (
        <div className="edit-note-page">

            <div className="edit-note-header">
                <div>
                    <h2>Edit Note</h2>

                    <p>
                        Update your note information.
                    </p>
                </div>
            </div>

            {error && (
                <div className="edit-note-error">
                    {error}
                </div>
            )}

            <form
                className="edit-note-form"
                onSubmit={handleSubmit}
            >

                <div className="form-group">
                    <label htmlFor="title">
                        Title
                    </label>

                    <input
                        id="title"
                        type="text"
                        value={title}
                        onChange={(e) =>
                            setTitle(e.target.value)
                        }
                        required
                    />
                </div>

                <div className="form-group">
                    <label htmlFor="content">
                        Content
                    </label>

                    <textarea
                        id="content"
                        value={content}
                        onChange={(e) =>
                            setContent(e.target.value)
                        }
                        rows={8}
                        required
                    />
                </div>

                {currentImage && (
                    <div className="current-image">
                        <label>
                            Current image
                        </label>

                        <img
                            src={currentImage}
                            alt={title}
                        />
                    </div>
                )}

                <div className="form-group">
                    <label htmlFor="image">
                        Replace image
                    </label>

                    <input
                        id="image"
                        type="file"
                        accept="image/*"
                        onChange={(e) => {
                            setImage(
                                e.target.files?.[0] ?? null
                            );
                        }}
                    />
                </div>

                <div className="edit-note-actions">

                    <button
                        type="button"
                        className="cancel-button"
                        onClick={() =>
                            navigate("/dashboard/notes")
                        }
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        className="save-button"
                        disabled={saving}
                    >
                        {saving
                            ? "Saving..."
                            : "Save Changes"}
                    </button>

                </div>

            </form>

        </div>
    );
}