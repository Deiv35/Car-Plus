<?php
session_start();

// Solo permitir acceso si hay sesión iniciada
if (!isset($_SESSION["usuario"])) {
    header("Location: Index.php");
    exit();
}

// Conexión con SQL Server
$serverName = "db28471.public.databaseasp.net"; 
    $connectionOptions = [
        "Database" => "db28471",
        "Uid" => "db28471",     // Usuario que creaste en SSMS
        "PWD" => "2Fb%y9-EH_z7",     // Contraseña que le diste
        "CharacterSet" => "UTF-8"
    ];
$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

$mensaje = "";
$vehiculoEditar = null;

# ✅ INSERTAR VEHÍCULO
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["agregar"])) {
    $sql = "INSERT INTO Vehiculos (Placa, Marca, Modelo, Anio, Color, NumeroChasis, NumeroMotor, Observaciones)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $params = [
        $_POST["placa"], $_POST["marca"], $_POST["modelo"], $_POST["anio"],
        $_POST["color"], $_POST["chasis"], $_POST["motor"], $_POST["observaciones"]
    ];
    $stmt = sqlsrv_query($conn, $sql, $params);
    $mensaje = $stmt ? "✅ Vehículo agregado correctamente." : "❌ Error al agregar: " . print_r(sqlsrv_errors(), true);
}

# ✅ EDITAR: cargar datos en formulario
if (isset($_GET["editar"])) {
    $id = intval($_GET["editar"]);
    $sql = "SELECT * FROM Vehiculos WHERE IdVehiculo = ?";
    $stmt = sqlsrv_query($conn, $sql, [$id]);
    if ($stmt && $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $vehiculoEditar = $row;
    }
}

# ✅ ACTUALIZAR VEHÍCULO
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["actualizar"])) {
    $id = intval($_POST["id"]);
    $sql = "UPDATE Vehiculos
            SET Placa=?, Marca=?, Modelo=?, Anio=?, Color=?, NumeroChasis=?, NumeroMotor=?, Observaciones=?
            WHERE IdVehiculo=?";
    $params = [
        $_POST["placa"], $_POST["marca"], $_POST["modelo"], $_POST["anio"],
        $_POST["color"], $_POST["chasis"], $_POST["motor"], $_POST["observaciones"], $id
    ];
    $stmt = sqlsrv_query($conn, $sql, $params);
    $mensaje = $stmt ? "✅ Vehículo actualizado correctamente." : "❌ Error al actualizar: " . print_r(sqlsrv_errors(), true);
}

# ✅ ELIMINAR VEHÍCULO
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["eliminar"])) {
    $id = intval($_POST["id"]);
    $sql = "DELETE FROM Vehiculos WHERE IdVehiculo = ?";
    $stmt = sqlsrv_query($conn, $sql, [$id]);
    $mensaje = $stmt ? "🗑 Vehículo eliminado correctamente." : "❌ Error al eliminar: " . print_r(sqlsrv_errors(), true);
}

# ✅ LISTADO
$sql = "SELECT * FROM Vehiculos";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Vehículos</title>
  <style>
    /* Fondo general en modo oscuro */
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #1e1e1e;
      color: #f0f0f0;
      text-align: center;
    }

    /* Encabezado */
    h2 {
      margin: 20px 0 10px;
      color: #f7cbcb;
      font-weight: bold;
      font-size: 28px;
      text-shadow: 
        -1px -1px 0 #ff3b3b,
         1px -1px 0 #ff3b3b,
        -1px  1px 0 #ff3b3b,
         1px  1px 0 #ff3b3b;
    }

    h3 {
      color: #ff3b3b;
      margin-bottom: 15px;
    }

    /* Logo */
    .logo {
      width: 120px;
      margin: 20px auto;
      display: block;
    }

    /* Formulario */
    form {
      width: 90%;
      max-width: 800px;
      margin: 20px auto;
      padding: 20px;
      border-radius: 12px;
      background: #2a2a2a;
      box-shadow: 0 0 12px rgba(0, 0, 0, 0.6);
    }

    input, textarea {
      margin: 6px;
      padding: 8px;
      width: 200px;
      border: none;
      border-radius: 6px;
      background: #3b3b3b;
      color: #f0f0f0;
    }

    input:focus, textarea:focus {
      outline: none;
      border: 1px solid #ff3b3b;
      background: #444;
    }

    input[type="submit"], .btn-danger, form a {
      display: inline-block;
      margin: 8px 5px;
      padding: 10px 16px;
      border: none;
      border-radius: 8px;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s ease;
      text-decoration: none;
    }

    input[type="submit"] {
      background: #ff3b3b;
      color: #fff;
    }
    input[type="submit"]:hover {
      background: #cc2e2e;
      transform: translateY(-2px);
    }

    .btn-danger {
      background: #444;
      color: #f0f0f0;
    }
    .btn-danger:hover {
      background: #666;
      color: #fff;
    }

    /* Contenedor de botones */
    .btn-group {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin-top: 15px;
    }

    /* Tabla */
    table {
      border-collapse: collapse;
      width: 90%;
      margin: 20px auto;
      background: #2a2a2a;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 0 12px rgba(0, 0, 0, 0.6);
    }

    th, td {
      border: 1px solid #444;
      padding: 10px;
      text-align: center;
    }

    th {
      background: #ff3b3b;
      color: #fff;
    }

    tr {
      cursor: pointer;
      transition: background 0.3s ease;
    }

    tr:hover {
      background: #3b3b3b;
    }

    /* Mensaje */
    .msg {
      text-align: center;
      font-weight: bold;
      color: #6df76d;
    }

    /* Enlace volver */
    .volver {
      position: absolute;
      top: 15px;
      left: 15px;
      color: #ff3b3b;
      text-decoration: none;
      font-weight: bold;
      transition: 0.3s;
    }
    .volver:hover {
      color: #cc2e2e;
    }
  </style>
</head>
<body>
  <!-- Botón volver -->
  <a href="Vendedor.php" class="volver">⬅ Volver al Panel Vendedor</a>

  <!-- Logo -->
  <img src="logo.png" alt="Logo Auto Parts" class="logo">

  <h2>Gestión de Vehículos</h2>

  <?php if (!empty($mensaje)) echo "<p class='msg'>$mensaje</p>"; ?>

  <!-- Formulario para agregar/editar/eliminar -->
  <form method="POST" action="Vehiculo.php">
    <h3><?php echo $vehiculoEditar ? "Editar Vehículo" : "Agregar Vehículo"; ?></h3>

    <?php if ($vehiculoEditar) { ?>
      <input type="hidden" name="id" value="<?php echo $vehiculoEditar['IdVehiculo']; ?>">
    <?php } ?>

    <input type="text" name="placa" placeholder="Placa" required 
           value="<?php echo $vehiculoEditar['Placa'] ?? ''; ?>">
    <input type="text" name="marca" placeholder="Marca" required 
           value="<?php echo $vehiculoEditar['Marca'] ?? ''; ?>">
    <input type="text" name="modelo" placeholder="Modelo" required 
           value="<?php echo $vehiculoEditar['Modelo'] ?? ''; ?>">
    <input type="number" name="anio" placeholder="Año" required 
           value="<?php echo $vehiculoEditar['Anio'] ?? ''; ?>">
    <input type="text" name="color" placeholder="Color" 
           value="<?php echo $vehiculoEditar['Color'] ?? ''; ?>">
    <input type="text" name="chasis" placeholder="N° Chasis" 
           value="<?php echo $vehiculoEditar['NumeroChasis'] ?? ''; ?>">
    <input type="text" name="motor" placeholder="N° Motor" 
           value="<?php echo $vehiculoEditar['NumeroMotor'] ?? ''; ?>">
    <textarea name="observaciones" placeholder="Observaciones" rows="2"><?php echo $vehiculoEditar['Observaciones'] ?? ''; ?></textarea>
    <br>

    <div class="btn-group">
      <input type="submit" name="agregar" value="Agregar Vehículo">
      <?php if ($vehiculoEditar) { ?>
        <input type="submit" name="actualizar" value="Actualizar Vehículo">
        <button type="submit" name="eliminar" class="btn-danger" onclick="return confirm('¿Seguro que deseas eliminar este vehículo?');">Eliminar Vehículo</button>
      <?php } ?>
    </div>
  </form>

  <!-- Listado de vehículos -->
  <table>
    <tr>
      <th>ID</th>
      <th>Placa</th>
      <th>Marca</th>
      <th>Modelo</th>
      <th>Año</th>
      <th>Color</th>
      <th>Chasis</th>
      <th>Motor</th>
      <th>Fecha Ingreso</th>
      <th>Observaciones</th>
    </tr>
    <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { ?>
      <tr onclick="window.location.href='Vehiculo.php?editar=<?php echo $row['IdVehiculo']; ?>'">
        <td><?php echo $row["IdVehiculo"]; ?></td>
        <td><?php echo $row["Placa"]; ?></td>
        <td><?php echo $row["Marca"]; ?></td>
        <td><?php echo $row["Modelo"]; ?></td>
        <td><?php echo $row["Anio"]; ?></td>
        <td><?php echo $row["Color"]; ?></td>
        <td><?php echo $row["NumeroChasis"]; ?></td>
        <td><?php echo $row["NumeroMotor"]; ?></td>
        <td><?php echo $row["FechaIngreso"] ? $row["FechaIngreso"]->format("Y-m-d") : ""; ?></td>
        <td><?php echo $row["Observaciones"]; ?></td>
      </tr>
    <?php } ?>
  </table>
</body>
</html>


