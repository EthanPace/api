<?php

# index.php
# Main entry point for the API, contains all the routes

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

require '../config.php'; # This is in the gitignore, contains the database credentials

# These should be autoloaded, but that's not working for me :(
require '../classes/Mapper.php';
require '../classes/NoteMapper.php';
require '../classes/NoteEntity.php';

# Create a new Slim app
$app = new \Slim\App(['settings' => $config]);

# Set up dependencies
$container = $app->getContainer();

# Register the database connection with the container
$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO('mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'], $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

######## Routes ########
# Get all notes
$app->get('/notes', function (Request $request, Response $response) {
    $mapper = new NoteMapper($this->db);
    try {
        $notes = $mapper->getNotes();
        
        $notesArray = array_map(function($note) {
            return $note->toArray();
        }, $notes);
        
        return $response->withStatus(200)->withJson($notesArray);
    } catch (Exception $e) {
        return $response->withStatus(500)->withJson(['error' => 'Failed to get notes']);
    }
});
# Get a single note by ID
$app->get('/notes/{id}', function (Request $request, Response $response, array $args) {
    $note_id = (int)$args['id'];

    $mapper = new NoteMapper($this->db);
    $note = $mapper->getNoteById($note_id);
    
    if (!$note) {
        return $response->withStatus(204)->withJson(['error' => 'Note not found']);
    }
    
    return $response->withStatus(200)->withJson($note->toArray());
});
# Create a new note
$app->post('/new', function (Request $request, Response $response) {
    # Get the data from the request
    $data = $request->getParsedBody();
    # Sanitize the data
    $note_data = [];
    $note_data['title'] = filter_var($data['title'], FILTER_SANITIZE_STRING);
    $note_data['content'] = filter_var($data['content'], FILTER_SANITIZE_STRING);
    $note_data['created_at'] = date('Y-m-d H:i:s');
    $note_data['updated_at'] = date('Y-m-d H:i:s');
    # Create a new NoteEntity object
    $note = new NoteEntity($data);
    # Create a new NoteMapper object
    $mapper = new NoteMapper($this->db);
    # Try to create the note, return a 500 error if it fails
    try {
        # Insert the note into the database
        $newNote = $mapper->createNote($note);
        # Return the new note
        return $response->withStatus(201)->withJson($newNote->toArray());
    } catch (Exception $e) {
        # Return a 500 error if the note creation fails
        return $response->withStatus(500)->withJson(['error' => 'Failed to create note']);
    }
});
# Update an existing note
$app->put('/edit/{id}', function (Request $request, Response $response, array $args) {
    # Get the data from the request
    $data = $request->getParsedBody();
    $note_id = (int)$args['id'];
    # Create a new NoteMapper object
    $mapper = new NoteMapper($this->db);
    # Sanitize the data
    $note_data = [];
    $note_data['id'] = $note_id;
    $note_data['title'] = filter_var($data['title'], FILTER_SANITIZE_STRING);
    $note_data['content'] = filter_var($data['content'], FILTER_SANITIZE_STRING);
    $note_data['created_at'] = $mapper->getNoteById($note_id)->getCreated();
    $note_data['updated_at'] = date('Y-m-d H:i:s');
    # Create a new NoteEntity object
    $note = new NoteEntity($note_data);
    # Try to update the note, return a 500 error if it fails
    try {
        # Update the note in the database
        $updatedNote = $mapper->updateNote($note);
        # Return the updated note
        return $response->withStatus(200)->withJson($updatedNote->toArray());
    } catch (Exception $e) {
        # Return a 500 error if the note update fails
        return $response->withStatus(500)->withJson(['error' => 'Failed to update note']);
    }
});
# Delete a note
$app->delete('/delete/{id}', function (Request $request, Response $response, array $args) {
    $note_id = (int)$args['id'];
    $mapper = new NoteMapper($this->db);
    try {
        $mapper->deleteNote($note_id);
        return $response->withStatus(204);
    } catch (Exception $e) {
        return $response->withStatus(500)->withJson(['error' => 'Failed to delete note']);
    }
});
# Reset the auto increment
$app->post('/reset_auto_increment', function (Request $request, Response $response) {
    $mapper = new NoteMapper($this->db);
    try {
        $mapper->resetAutoIncrement();
        return $response->withStatus(200)->withJson(['message' => 'Auto increment reset']);
    } catch (Exception $e) {
        return $response->withStatus(500)->withJson(['error' => 'Failed to reset auto increment']);
    }
});

# Run the app
$app->run();