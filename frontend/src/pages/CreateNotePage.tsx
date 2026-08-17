import { useState } from "react";
import { useNavigate } from "react-router-dom";

import { createNote } from "../api/notesApi";

import "../css/CreateNotePage.css";

export default function CreateNotePage() {

    const navigate = useNavigate();

    const [title, setTitle] = useState("");
    const [content, setContent] = useState("");

    const [image, setImage] =
        useState<File | null>(null);

    const [loading, setLoading] = useState(false);
    const [error, setError] = useState("");

    const handleImageChange = (
        event: React.ChangeEvent<HTMLInputElement>
    ) => {

        const file =
            event.target.files?.[0] ?? null;

        setImage(file);
    };

    const handleSubmit = async (
        event: React.FormEvent
    ) => {

        event.preventDefault();

        setError("");

        if (!title.trim()) {
            setError("Please enter a title.");
            return;
        }

        if (!content.trim()) {
            setError("Please enter some content.");
            return;
        }

        try {

            setLoading(true);

            await createNote({
                title: title.trim(),
                content: content.trim(),
                image,
            });

            navigate("/dashboard/notes");

        } catch (error) {

            console.error(
                "Failed to create note:",
                error
            );

            setError(
                "Unable to create note. Please try again."
            );

        } finally {

            setLoading(false);

        }
    };

    return (
        <div className="create-note-page">

            <div className="create-note-header">

                <div>
                    <h2>Create Note</h2>

                    <p>
                        Create a new note and optionally
                        attach an image.
                    </p>
                </div>

            </div>

            <div className="create-note-card">

                {error && (
                    <div className="create-note-error">
                        {error}
                    </div>
                )}

                <form onSubmit={handleSubmit}>

                    {/* Title */}

                    <div className="form-group">

                        <label htmlFor="title">
                            Title
                        </label>

                        <input
                            id="title"
                            type="text"
                            value={title}
                            onChange={(event) =>
                                setTitle(event.target.value)
                            }
                            placeholder="Enter note title"
                            disabled={loading}
                        />

                    </div>


                    {/* Content */}

                    <div className="form-group">

                        <label htmlFor="content">
                            Content
                        </label>

                        <textarea
                            id="content"
                            value={content}
                            onChange={(event) =>
                                setContent(event.target.value)
                            }
                            placeholder="Write your note..."
                            rows={8}
                            disabled={loading}
                        />

                    </div>


                    {/* Image */}

                    <div className="form-group">

                        <label htmlFor="image">
                            Image
                        </label>

                        <input
                            id="image"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            onChange={handleImageChange}
                            disabled={loading}
                        />

                        {image && (
                            <div className="selected-image">
                                Selected: {image.name}
                            </div>
                        )}

                    </div>


                    {/* Actions */}

                    <div className="create-note-actions">

                        <button
                            type="button"
                            className="cancel-button"
                            onClick={() =>
                                navigate("/dashboard/notes")
                            }
                            disabled={loading}
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            className="create-note-button"
                            disabled={loading}
                        >
                            {loading
                                ? "Creating..."
                                : "Create Note"}
                        </button>

                    </div>

                </form>

            </div>

        </div>
    );
}