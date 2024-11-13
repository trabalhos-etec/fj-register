<?php
// Configurações do banco de dados
$servername = "gateway01.us-east-1.prod.aws.tidbcloud.com";
$port = 4000;
$username = "22QFigmfZypwVeu.root";
$password = "MwpaUDqIBb2157HN";
$dbname = "fit_journey_db";

// Caminho para o arquivo de certificados do sistema
$caCert = '/etc/ssl/certs/ca-certificates.crt'; // Certificado da autoridade (CA)

header('Content-Type: application/json'); // Define o tipo de conteúdo da resposta como JSON

try {
    $conn = new PDO("mysql:host=$servername;port=$port;dbname=$dbname", $username, $password, [
        PDO::MYSQL_ATTR_SSL_CA => $caCert,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    // Se a conexão for bem-sucedida, não faça nada. Continuamos o processo.
} catch (PDOException $e) {
    // Trata erro de conexão
    echo json_encode(["success" => false, "message" => "Erro ao conectar: " . $e->getMessage()]);
    exit;
}

// Verifica se os dados foram enviados via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lê o corpo da requisição e decodifica o JSON
    $inputData = json_decode(file_get_contents("php://input"), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        // Se houver um erro na decodificação do JSON
        echo json_encode(["success" => false, "message" => "Erro ao decodificar JSON: " . json_last_error_msg()]);
        exit;
    }

    // Recebe os dados do JSON
    $email = $inputData['email'] ?? null;
    $password = $inputData['password'] ?? null; // Recebe a senha
    $name = $inputData['name'] ?? null;
    $surname = $inputData['surname'] ?? null;
    $age = $inputData['age'] ?? null;
    $profileImage = $inputData['profileImage'] ?? null; // Pode ser um caminho ou URL de imagem
    $height = $inputData['height'] ?? null;
    $weight = $inputData['weight'] ?? null;
    $gender = $inputData['gender'] ?? null;

    // Verifique se todos os campos obrigatórios estão presentes
    if (!$email || !$password || !$name || !$surname || !$age || !$height || !$weight || !$gender) {
        echo json_encode(["success" => false, "message" => "Campos obrigatórios não preenchidos."]);
        exit;
    }

    // Gera o hash da senha
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Cria a query SQL para inserir no banco
    $query = "INSERT INTO users (email, name, surname, age, profile_image, height, weight, gender, password_hash) 
              VALUES (:email, :name, :surname, :age, :profileImage, :height, :weight, :gender, :passwordHash)";

    // Prepara a consulta
    $stmt = $conn->prepare($query);
    
    // Bind dos parâmetros
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':surname', $surname);
    $stmt->bindParam(':age', $age);
    $stmt->bindParam(':profileImage', $profileImage);
    $stmt->bindParam(':height', $height);
    $stmt->bindParam(':weight', $weight);
    $stmt->bindParam(':gender', $gender);
    $stmt->bindParam(':passwordHash', $passwordHash);

    // Executa a consulta
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Usuário cadastrado com sucesso!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Erro ao cadastrar o usuário."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método não permitido. Apenas POST é permitido."]);
}
?>
