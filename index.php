<?php
// Configurações do banco de dados
$servername = "gateway01.us-east-1.prod.aws.tidbcloud.com";
$port = 4000;
$username = "22QFigmfZypwVeu.root";
$password = "MwpaUDqIBb2157HN";
$dbname = "fit_journey_db";

// Caminho para o arquivo de certificados do sistema
$caCert = '/etc/ssl/certs/ca-certificates.crt'; // Certificado da autoridade (CA)

try {
    $conn = new PDO("mysql:host=$servername;port=$port;dbname=$dbname", $username, $password, [
        PDO::MYSQL_ATTR_SSL_CA => $caCert,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "Conectado ao banco de dados!";
} catch (PDOException $e) {
    // Trata erro de conexão
    echo "Erro ao conectar: " . $e->getMessage();
}

// Verifica se os dados foram enviados via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recebe os dados
    $email = $_POST['email'];
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $age = $_POST['age'];
    $profileImage = $_POST['profileImage']; // Aqui você pode salvar o caminho da imagem ou o conteúdo da imagem
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $gender = $_POST['gender'];

    // Cria a query SQL para inserir no banco
    $query = "INSERT INTO users (email, name, surname, age, profile_image, height, weight, gender) 
              VALUES (:email, :name, :surname, :age, :profileImage, :height, :weight, :gender)";

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

    // Executa a consulta
    if ($stmt->execute()) {
        echo "Usuário cadastrado com sucesso!";
    } else {
        echo "Erro ao cadastrar o usuário.";
    }
} else {
    echo "Método não permitido. Apenas POST é permitido.";
}
?>
