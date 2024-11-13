<?php
header('Content-Type: application/json');

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
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erro ao conectar: " . $e->getMessage()]);
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

    // Verifique se todos os campos obrigatórios estão presentes
    if (!$email || !$password || !$name || !$surname || !$age || !$height || !$weight || !$gender) {
        echo json_encode(["success" => false, "message" => "Campos obrigatórios não preenchidos."]);
        exit;
    }

    // Verifique se o email já existe na base de dados
    $query = "SELECT * FROM users WHERE email = :email";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $existingUser = $stmt->fetch();

    if ($existingUser) {
        echo json_encode(["success" => false, "message" => "Este email já está em uso."]);
        exit;
    }

    // Verifica se o arquivo foi enviado
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
        // Diretório onde as imagens serão armazenadas
        $target_dir = "uploads/";  // Ou o diretório adequado em seu servidor
        $target_file = $target_dir . basename($_FILES["profileImage"]["name"]);
        $uploadOk = 1;

        // Verifica se o arquivo é uma imagem real
        $check = getimagesize($_FILES["profileImage"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            echo json_encode(["success" => false, "message" => "O arquivo não é uma imagem."]);
            $uploadOk = 0;
        }

        // Verifica se o arquivo já existe
        if (file_exists($target_file)) {
            echo json_encode(["success" => false, "message" => "Desculpe, o arquivo já existe."]);
            $uploadOk = 0;
        }

        // Verifica o tamanho do arquivo
        if ($_FILES["profileImage"]["size"] > 5000000) { 
            echo json_encode(["success" => false, "message" => "Desculpe, o arquivo é muito grande."]);
            $uploadOk = 0;
        }

        // Se tudo estiver ok, move o arquivo para o diretório
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["profileImage"]["tmp_name"], $target_file)) {
                echo json_encode(["success" => true, "message" => "O arquivo ". basename($_FILES["profileImage"]["name"]). " foi carregado com sucesso."]);

                // Cria a query SQL para inserir no banco
                $query = "INSERT INTO users (email, name, surname, age, profile_image, height, weight, gender, password) 
                          VALUES (:email, :name, :surname, :age, :profileImage, :height, :weight, :gender, :password)";

                // Prepara a consulta
                $stmt = $conn->prepare($query);

                // Bind dos parâmetros
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':surname', $surname);
                $stmt->bindParam(':age', $age);
                $stmt->bindParam(':profileImage', $target_file); // Caminho da imagem
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
                echo json_encode(["success" => false, "message" => "Desculpe, houve um erro ao carregar o arquivo."]);
            }
        }
    } else {
        echo json_encode(["success" => false, "message" => "Arquivo não enviado ou erro no envio."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método não permitido. Apenas POST é permitido."]);
}
?>
