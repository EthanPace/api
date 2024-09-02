<?php

class NoteEntity
{
    protected $id;
    protected $title;
    protected $content;
    protected $created_at;
    protected $updated_at;

    public function __construct(array $data) {
        if(isset($data['id'])) {
            $this->id = $data['id'];
        }
        $this->title = $data['title'];
        $this->content = $data['content'];
        $this->created_at = $data['created_at'];
        $this->updated_at = $data['updated_at'];
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getId() {
        return $this->id;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getContent() {
        return $this->content;
    }

    public function getCreated() {
        return $this->created_at;
    }

    public function getUpdated() {
        return $this->updated_at;
    }

    public function toArray() {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}