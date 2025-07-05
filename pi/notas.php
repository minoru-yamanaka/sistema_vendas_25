<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplicativo de Anotações</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            color: #374151;
        }
        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: #ffffff;
            border-radius: 0.75rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 1rem;
            color: #4b5563;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
        }
        .btn-primary {
            background-color: #4f46e5;
            color: #ffffff;
        }
        .btn-primary:hover {
            background-color: #4338ca;
        }
        .btn-secondary {
            background-color: #6b7280;
            color: #ffffff;
        }
        .btn-secondary:hover {
            background-color: #4b5563;
        }
        .note-card {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body class="p-4">
    <div class="container">
        <h1 class="text-3xl font-bold text-center mb-8 text-gray-800">Meu Aplicativo de Anotações</h1>

        <?php
        // Database configuration
        define('DB_HOST', 'localhost');
        define('DB_USER', 'root'); // Change if necessary
        define('DB_PASS', '');     // Change if necessary
        define('DB_NAME', 'notes_db'); // Change if necessary

        // --- 1. Database Setup (database.sql content) ---
        /*
        -- SQL para criar o banco de dados e a tabela 'notes'
        CREATE DATABASE IF NOT EXISTS notes_db;
        USE notes_db;

        CREATE TABLE IF NOT EXISTS notes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            note_date DATE NOT NULL,
            note_content TEXT NOT NULL
        );
        */

        // --- 2. Note.php Class ---
        class Note {
            private $id;
            private $name;
            private $noteDate;
            private $noteContent;

            public function __construct($id = null, $name = null, $noteDate = null, $noteContent = null) {
                $this->id = $id;
                $this->name = $name;
                $this->noteDate = $noteDate;
                $this->noteContent = $noteContent;
            }

            // Getters
            public function getId() {
                return $this->id;
            }

            public function getName() {
                return $this->name;
            }

            public function getNoteDate() {
                return $this->noteDate;
            }

            public function getNoteContent() {
                return $this->noteContent;
            }

            // Setters
            public function setId($id) {
                $this->id = $id;
            }

            public function setName($name) {
                $this->name = $name;
            }

            public function setNoteDate($noteDate) {
                $this->noteDate = $noteDate;
            }

            public function setNoteContent($noteContent) {
                $this->noteContent = $noteContent;
            }
        }

        // --- 3. NoteDAO.php Class ---
        class NoteDAO {
            private $conn;

            public function __construct() {
                try {
                    $this->conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
                    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (PDOException $e) {
                    die("Erro de conexão com o banco de dados: " . $e->getMessage());
                }
            }

            public function addNote(Note $note) {
                $stmt = $this->conn->prepare("INSERT INTO notes (name, note_date, note_content) VALUES (:name, :note_date, :note_content)");
                $stmt->bindValue(':name', $note->getName());
                $stmt->bindValue(':note_date', $note->getNoteDate());
                $stmt->bindValue(':note_content', $note->getNoteContent());
                $stmt->execute();
                return $this->conn->lastInsertId();
            }

            public function getAllNotes() {
                $stmt = $this->conn->query("SELECT * FROM notes ORDER BY note_date DESC, id DESC");
                return $stmt->fetchAll(PDO::FETCH_CLASS, 'Note');
            }

            public function filterNotesByDate($date) {
                $stmt = $this->conn->prepare("SELECT * FROM notes WHERE note_date = :date ORDER BY note_date DESC, id DESC");
                $stmt->bindValue(':date', $date);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_CLASS, 'Note');
            }

            public function filterNotesByKeyword($keyword) {
                $stmt = $this->conn->prepare("SELECT * FROM notes WHERE name LIKE :keyword OR note_content LIKE :keyword ORDER BY note_date DESC, id DESC");
                $stmt->bindValue(':keyword', '%' . $keyword . '%');
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_CLASS, 'Note');
            }
        }

        // --- 4. index.php Logic ---
        $noteDAO = new NoteDAO();
        $notes = [];
        $filterMessage = '';

        // Handle Add Note
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_note'])) {
            $name = trim($_POST['name']);
            $noteDate = trim($_POST['note_date']);
            $noteContent = trim($_POST['note_content']);

            if (!empty($name) && !empty($noteDate) && !empty($noteContent)) {
                $newNote = new Note(null, $name, $noteDate, $noteContent);
                $noteDAO->addNote($newNote);
                $filterMessage = '<p class="text-green-600 text-center mb-4">Anotação adicionada com sucesso!</p>';
            } else {
                $filterMessage = '<p class="text-red-600 text-center mb-4">Por favor, preencha todos os campos para adicionar uma anotação.</p>';
            }
        }

        // Handle Filter Notes
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['filter_notes'])) {
            $filterDate = trim($_GET['filter_date']);
            $filterKeyword = trim($_GET['filter_keyword']);

            if (!empty($filterDate)) {
                $notes = $noteDAO->filterNotesByDate($filterDate);
                $filterMessage = '<p class="text-blue-600 text-center mb-4">Filtrando por data: ' . htmlspecialchars($filterDate) . '</p>';
            } elseif (!empty($filterKeyword)) {
                $notes = $noteDAO->filterNotesByKeyword($filterKeyword);
                $filterMessage = '<p class="text-blue-600 text-center mb-4">Filtrando por palavra-chave: "' . htmlspecialchars($filterKeyword) . '"</p>';
            } else {
                $notes = $noteDAO->getAllNotes(); // Show all if no filter or empty filter
                $filterMessage = '<p class="text-gray-600 text-center mb-4">Exibindo todas as anotações.</p>';
            }
        } else {
            // Default: show all notes on initial load or if no filter applied
            $notes = $noteDAO->getAllNotes();
            $filterMessage = '<p class="text-gray-600 text-center mb-4">Exibindo todas as anotações.</p>';
        }
        ?>

        <?php echo $filterMessage; ?>

        <!-- Add Note Form -->
        <div class="mb-8 p-6 bg-gray-50 rounded-lg shadow-inner">
            <h2 class="text-2xl font-semibold mb-4 text-gray-700">Adicionar Nova Anotação</h2>
            <form method="POST" action="" class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Seu Nome:</label>
                    <input type="text" id="name" name="name" required class="form-input rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="note_date" class="block text-sm font-medium text-gray-700 mb-1">Data:</label>
                    <input type="date" id="note_date" name="note_date" required class="form-input rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="note_content" class="block text-sm font-medium text-gray-700 mb-1">Anotação:</label>
                    <textarea id="note_content" name="note_content" rows="4" required class="form-input rounded-md focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>
                <button type="submit" name="add_note" class="btn btn-primary w-full md:w-auto">Adicionar Anotação</button>
            </form>
        </div>

        <!-- Filter Notes Form -->
        <div class="mb-8 p-6 bg-gray-50 rounded-lg shadow-inner">
            <h2 class="text-2xl font-semibold mb-4 text-gray-700">Filtrar Anotações</h2>
            <form method="GET" action="" class="space-y-4 md:flex md:space-y-0 md:space-x-4">
                <div class="flex-1">
                    <label for="filter_date" class="block text-sm font-medium text-gray-700 mb-1">Filtrar por Data:</label>
                    <input type="date" id="filter_date" name="filter_date" class="form-input rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="flex-1">
                    <label for="filter_keyword" class="block text-sm font-medium text-gray-700 mb-1">Filtrar por Palavra-chave:</label>
                    <input type="text" id="filter_keyword" name="filter_keyword" placeholder="Ex: reunião, projeto" class="form-input rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="flex items-end">
                    <button type="submit" name="filter_notes" class="btn btn-secondary w-full md:w-auto">Filtrar</button>
                </div>
            </form>
        </div>

        <!-- Notes List -->
        <div class="p-6 bg-gray-50 rounded-lg shadow-inner">
            <h2 class="text-2xl font-semibold mb-4 text-gray-700">Minhas Anotações</h2>
            <?php if (empty($notes)): ?>
                <p class="text-center text-gray-500">Nenhuma anotação encontrada.</p>
            <?php else: ?>
                <?php foreach ($notes as $note): ?>
                    <div class="note-card">
                        <h3 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($note->getName()); ?></h3>
                        <p class="text-sm text-gray-600 mb-2">Data: <?php echo htmlspecialchars($note->getNoteDate()); ?></p>
                        <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($note->getNoteContent())); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- https://gemini.google.com/app/0838c72fcd8fc348 -->

</body>
</html>