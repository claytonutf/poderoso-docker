<?php
// Configurações do banco de dados
$host = $_ENV['DB_HOST'] ?? 'database';
$port = $_ENV['DB_PORT'] ?? '3306';
$dbname = $_ENV['DB_NAME'] ?? 'app_database';
$username = $_ENV['DB_USER'] ?? 'app_user';
$password = $_ENV['DB_PASSWORD'] ?? 'app_password';

try {
    // Conecta ao banco de dados
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// Variáveis para mensagens e formulário
$message = '';
$editUser = null;

// Processamento das ações CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            
            if (!empty($name) && !empty($email)) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
                        $stmt->execute([$name, $email]);
                        $message = "<div class='alert success'>Usuário criado com sucesso!</div>";
                    } catch (PDOException $e) {
                        if ($e->getCode() == 23000) {
                            $message = "<div class='alert error'>Este email já está cadastrado!</div>";
                        } else {
                            $message = "<div class='alert error'>Erro ao criar usuário: " . $e->getMessage() . "</div>";
                        }
                    }
                } else {
                    $message = "<div class='alert error'>Email inválido!</div>";
                }
            } else {
                $message = "<div class='alert error'>Nome e email são obrigatórios!</div>";
            }
            break;
            
        case 'update':
            $id = $_POST['id'] ?? '';
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            
            if (!empty($id) && !empty($name) && !empty($email)) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    try {
                        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                        $stmt->execute([$name, $email, $id]);
                        $message = "<div class='alert success'>Usuário atualizado com sucesso!</div>";
                    } catch (PDOException $e) {
                        if ($e->getCode() == 23000) {
                            $message = "<div class='alert error'>Este email já está sendo usado por outro usuário!</div>";
                        } else {
                            $message = "<div class='alert error'>Erro ao atualizar usuário: " . $e->getMessage() . "</div>";
                        }
                    }
                } else {
                    $message = "<div class='alert error'>Email inválido!</div>";
                }
            }
            break;
            
        case 'delete':
            $id = $_POST['id'] ?? '';
            if (!empty($id)) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$id]);
                    $message = "<div class='alert success'>Usuário excluído com sucesso!</div>";
                } catch (PDOException $e) {
                    $message = "<div class='alert error'>Erro ao excluir usuário: " . $e->getMessage() . "</div>";
                }
            }
            break;
    }
}

// Carrega usuário para edição
if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$editId]);
    $editUser = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Busca todos os usuários
try {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $users = [];
    $message = "<div class='alert error'>Erro ao buscar usuários: " . $e->getMessage() . "</div>";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD de Usuários - PHP + MariaDB</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 0;
            text-align: center;
            margin-bottom: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        
        .card h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #555;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
            color: white;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
            color: white;
        }
        
        .btn-small {
            padding: 8px 16px;
            font-size: 14px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-weight: 500;
        }
        
        .alert.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .actions {
            white-space: nowrap;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            flex: 1;
            margin: 0 10px;
        }
        
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #667eea;
        }
        
        .stat-label {
            color: #666;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
            }
            
            .stats {
                flex-direction: column;
            }
            
            .stat-card {
                margin: 10px 0;
            }
            
            .actions {
                white-space: wrap;
            }
            
            .btn {
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Sistema CRUD de Usuários</h1>
            <p>Gerenciamento completo de usuários com PHP 8 + MariaDB</p>
        </div>
        
        <?php if ($message): ?>
            <?php echo $message; ?>
        <?php endif; ?>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($users); ?></div>
                <div class="stat-label">Total de Usuários</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo phpversion(); ?></div>
                <div class="stat-label">Versão do PHP</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">MariaDB</div>
                <div class="stat-label">Banco de Dados</div>
            </div>
        </div>
        
        <div class="card">
            <h2><?php echo $editUser ? 'Editar Usuário' : 'Adicionar Novo Usuário'; ?></h2>
            
            <form method="POST" action="">
                <input type="hidden" name="action" value="<?php echo $editUser ? 'update' : 'create'; ?>">
                <?php if ($editUser): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($editUser['id']); ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Nome:</label>
                        <input type="text" id="name" name="name" required 
                               value="<?php echo $editUser ? htmlspecialchars($editUser['name']) : ''; ?>"
                               placeholder="Digite o nome completo">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo $editUser ? htmlspecialchars($editUser['email']) : ''; ?>"
                               placeholder="Digite o email">
                    </div>
                </div>
                
                <div class="form-group">
                    <?php if ($editUser): ?>
                        <button type="submit" class="btn btn-success">
                            ✅ Atualizar Usuário
                        </button>
                        <a href="index.php" class="btn btn-warning">
                            ❌ Cancelar Edição
                        </a>
                    <?php else: ?>
                        <button type="submit" class="btn btn-primary">
                            ➕ Adicionar Usuário
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="card">
            <h2>Lista de Usuários Cadastrados</h2>
            
            <?php if (empty($users)): ?>
                <p>Nenhum usuário cadastrado ainda. Adicione o primeiro usuário usando o formulário acima!</p>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Data de Cadastro</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                                    <td class="actions">
                                        <a href="?edit=<?php echo $user['id']; ?>" class="btn btn-warning btn-small">
                                            ✏️ Editar
                                        </a>
                                        
                                        <form method="POST" style="display: inline-block;" 
                                              onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-small">
                                                🗑️ Excluir
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Confirmação antes de excluir
        function confirmDelete(id) {
            if (confirm('Tem certeza que deseja excluir este usuário?')) {
                document.getElementById('deleteForm' + id).submit();
            }
        }
        
        // Auto-hide alerts após 5 segundos
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            });
        }, 5000);
    </script>
</body>
</html>