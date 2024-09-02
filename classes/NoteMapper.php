<?php

class NoteMapper extends Mapper
{
    /**
     * Get all notes
     *
     * @return array  An array of notes
     */
    public function getNotes() {
        $sql = "SELECT n.id, n.title, n.content, n.created_at, n.updated_at
            FROM notes n";
        $stmt = $this->db->query($sql);

        $results = [];
        while($row = $stmt->fetch()) {
            $results[] = new NoteEntity($row);
        }
        return $results;
    }
    /**
     * Get one note by its ID
     *
     * @param int $note_id The ID of the note
     * @return NoteEntity  The note
     */
    public function getNoteById($note_id) {
        $sql = "SELECT n.id, n.title, n.content, n.created_at, n.updated_at
            FROM notes n
            WHERE n.id = :note_id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(["note_id" => $note_id]);

        if($result) {
            $row = $stmt->fetch();
            if ($row) {
                return new NoteEntity($row);
            }
        }
        return null;
    }
    /**
     * Create a new note
     *
     * @param NoteEntity $note The note to create
     */
    public function createNote(NoteEntity $note) {
        $sql = "INSERT INTO notes
            (title, content, created_at, updated_at) VALUES
            (:title, :content, :created_at, :updated_at)";

        $stmt = $this->db->prepare($sql);
        try {
            $result = $stmt->execute([
                "title" => $note->getTitle(),
                "content" => $note->getContent(),
                "created_at" => $note->getCreated(),
                "updated_at" => $note->getUpdated(),
            ]);
            $note->setId($this->db->lastInsertId());
            return $note;
        } catch (Exception $e) {
            throw new Exception("Could not create record");
        }
    }
    /**
     * Update a note
     *
     * @param NoteEntity $note The note to update
     */
    public function updateNote(NoteEntity $note) {
        $sql = "UPDATE notes SET
            title = :title,
            content = :content,
            updated_at = :updated_at
            WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "title" => $note->getTitle(),
            "content" => $note->getContent(),
            "updated_at" => date('Y-m-d H:i:s'),
            "id" => $note->getId()
        ]);

        if(!$result) {
            throw new Exception("Could not update record");
        }
        return $note;
    }
    /**
     * Delete a note
     *
     * @param int $note_id The ID of the note to delete
     */
    public function deleteNote($note_id) {
        $sql = "DELETE FROM notes WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(["id" => $note_id]);

        if(!$result) {
            throw new Exception("Could not delete record");
        }
    }
    /**
     * Reset the auto incrementing ID
     */
    public function resetAutoIncrement() {
        $sql = "ALTER TABLE notes AUTO_INCREMENT = 1";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute();
        if(!$result) {
            throw new Exception("Could not reset auto increment");
        }
    }
}