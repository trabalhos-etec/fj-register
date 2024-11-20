<?php
// Ativa a exibição de erros
error_reporting(E_ALL); 
ini_set('display_errors', 1);
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '10M');

// Configurações do banco de dados
$servername = "gateway01.us-east-1.prod.aws.tidbcloud.com";
$port = 4000;
$username = "22QFigmfZypwVeu.root";
$password = "MwpaUDqIBb2157HN";
$dbname = "fit_journey_db";

// Caminho para o arquivo de certificados do sistema
$caCert = '/etc/ssl/certs/ca-certificates.crt'; // Certificado da autoridade (CA)

// Define o tipo de conteúdo da resposta como JSON
header('Content-Type: application/json');

// Tenta conectar ao banco de dados
try {
    $conn = new PDO("mysql:host=$servername;port=$port;dbname=$dbname", $username, $password, [
        PDO::MYSQL_ATTR_SSL_CA => $caCert,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erro ao conectar ao banco de dados: " . $e->getMessage()]);
    exit;
}

// Verifica se os dados foram enviados via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recupera os dados do POST
    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;
    $name = $_POST['name'] ?? null;
    $surname = $_POST['surname'] ?? null;
    $age = $_POST['age'] ?? null;
    $height = $_POST['height'] ?? null;
    $weight = $_POST['weight'] ?? null;
    $gender = $_POST['gender'] ?? null;

    // Verifica se todos os campos obrigatórios estão presentes
    if (!$email || !$password || !$name || !$surname || !$age || !$height || !$weight || !$gender) {
        echo json_encode(["success" => false, "message" => "Campos obrigatórios não preenchidos."]);
        exit;
    }

    // Verifica se o email já existe na base de dados
    $query = "SELECT * FROM users WHERE email = :email";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $existingUser = $stmt->fetch();

    if ($existingUser) {
        echo json_encode(["success" => false, "message" => "Este email já está em uso."]);
        exit;
    }

    // Verifica se o arquivo foi enviado corretamente
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
        // Lê o conteúdo do arquivo como BLOB
        $profileImageData = file_get_contents($_FILES['profileImage']['tmp_name']);

        // Verifica o tamanho do arquivo
        if ($_FILES['profileImage']['size'] > 5000000) { 
            echo json_encode(["success" => false, "message" => "Desculpe, o arquivo é muito grande."]);
            exit;
        }

        // Cria a query SQL para inserir no banco, agora com o BLOB
        $query = "INSERT INTO users (email, name, surname, age, profile_image, height, weight, gender, password) 
                  VALUES (:email, :name, :surname, :age, :profileImage, :height, :weight, :gender, :password)";

        // Prepara a consulta
        $stmt = $conn->prepare($query);

        // Bind dos parâmetros
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':surname', $surname);
        $stmt->bindParam(':age', $age);
        $stmt->bindParam(':profileImage', $profileImageData, PDO::PARAM_LOB); // BLOB
        $stmt->bindParam(':height', $height);
        $stmt->bindParam(':weight', $weight);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':password', $password);

        // Executa a consulta
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Usuário cadastrado com sucesso!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Erro ao cadastrar o usuário."]);
        }
    } else {
        // Mostra o código do erro do arquivo
        echo json_encode(["success" => false, "message" => "Arquivo não enviado ou erro no envio. Código de erro: " . $_FILES['profileImage']['error']]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método não permitido. Apenas POST é permitido."]);
}
?>

