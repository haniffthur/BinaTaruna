<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login</title>
  <style>
    :root {
      --primary-blue: #004aad;
      --accent-orange: #f7941e;
      --accent-green: #00a651;
      --accent-red: #ed1c24;
      --white: #ffffff;
      --bg-light: #f2f6fc;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', sans-serif;
    }

    body {
      height: 100vh;
      background-color: var(--bg-light);
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }

    .container {
      display: flex;
      background-color: #fff;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      overflow: hidden;
      max-width: 900px;
      width: 100%;
    }

    .left-side {
      flex: 1;
      background: url('/img/binatarunalogo.png') no-repeat center center;
      background-size: cover;
      min-height: 400px;
    }

    .right-side {
      flex: 1;
      padding: 40px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      background-color: #fff;
    }

    .right-side h2 {
      text-align: center;
      margin-bottom: 24px;
      font-size: 26px;
      color: var(--primary-blue);
    }

    .right-side label {
      display: block;
      margin-bottom: 8px;
      font-weight: bold;
      color: #444;
    }

    .right-side input {
      width: 100%;
      padding: 10px;
      margin-bottom: 16px;
      border: 1px solid #ccc;
      border-radius: 8px;
    }

    .right-side button {
      width: 100%;
      background-color: var(--primary-blue);
      color: var(--white);
      padding: 12px;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    .right-side button:hover {
      background-color: var(--accent-orange);
    }

    @media (max-width: 768px) {
      .container {
        flex-direction: column;
      }

      .left-side {
        height: 200px;
      }
    }
  </style>
</head>
<body>

  <div class="container">
    <div class="left-side"></div>
    <div class="right-side">
      <h2>Login</h2>
      <form method="POST" action="{{ route('login') }}">
        @csrf
        <label for="username">Username</label>
        <input type="text" name="username" id="username" required value="{{ old('username') }}"/>

        <label for="password">Password</label>
        <input type="password" name="password" id="password" required />

        <button type="submit">Login</button>
      </form>
    </div>
  </div>

  <!-- SweetAlert -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  @if(session('success'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: '{{ session('success') }}',
        confirmButtonColor: '#004aad'
      });
    </script>
  @endif

  @if(session('error'))
    <script>
      Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: '{{ session('error') }}',
        confirmButtonColor: '#ed1c24'
      });
    </script>
  @endif

</body>
</html>
