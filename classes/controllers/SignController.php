<?php

class SignController
{

  public static function sign_form()
  {
    if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
      if (isset($_POST['sign-up']))
      {
        SignController::sign_up();
      } elseif (isset($_POST['sign-in']))
      {
        SignController::sign_in();
      }
    }
    if (($_SERVER['REQUEST_METHOD'] == 'GET') && isset($_GET['signout']))
    {
      SignController::sign_out();
    }
    require('views/signView.php');
  }

  public static function sign_up()
  {
    $error = false;
    $user_id = $username = $password = $folder_name = $success_msg = $error_msg = '';

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sign-up']))
    {
      if (empty($_POST['username']))
      {
        $error = true;
        $error_msg .= 'username cannot be empty';
      } elseif (strlen($_POST['username']) < 3)
      {
        $error = true;
        $error_msg .= 'username must contain more than 6 characters';
      } else
      {
        $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
      }

      if (empty($_POST['folder-name']))
      {
        $error = true;
        $error_msg .= 'folder name cannot be empty';
      } elseif (strlen($_POST['folder-name']) < 6)
      {
        $error = true;
        $error_msg .= 'folder name must contain more than 6 characters';
      } else
      {
        $folder_name = filter_var($_POST['folder-name'], FILTER_SANITIZE_STRING);
      }

      if (empty($_POST['password']))
      {
        $error = true;
        $error_msg .= 'password cannot be empty';
      } elseif (strlen($_POST['password']) < 6)
      {
        $error = true;
        $error_msg .= 'password must contain more than 6 characters';
      } elseif (!preg_match("#[0-9]+#", $_POST['password']))
      {
        $error = true;
        $error_msg .= 'password must contain at least one number!';
      } elseif (!preg_match("#[a-z]+#", $_POST['password']))
      {
        $error = true;
        $error_msg .= 'password must contain at least one lowercase character!';
      } elseif (!preg_match("#[A-Z]+#", $_POST['password']))
      {
        $error = true;
        $error_msg .= 'password must contain at least one uppercase character!';
      } elseif (!preg_match("#\W+#", $_POST['password']))
      {
        $error = true;
        $error_msg .= 'password must contain at least one symbol!';
      } elseif ($_POST['password'] !== $_POST['confirm-password'])
      {
        $error = true;
        $error_msg .= 'passwords do not match';
      } else
      {
        $options = [
          'cost' => 12,
        ];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT, $options);
      }
    }

    if ($error === false)
    {
      if ((new SignModel())->user_exists($username) === false)
      {
        $user_id = (new SignModel())->create_new_user($username, $password);
        (new SignModel())->create_user_folder($user_id, $folder_name);

        $_SESSION['user'] = $username;
        $_SESSION['folder'] = $folder_name;
        $_SESSION['logged_in'] = true;

        $success_msg .= 'connexion successful!';
        header("Location:/folder?success=$success_msg");
      } else
      {
        $error_msg .= 'username already exists';
        header("Location:/?error=$error_msg");
      }
    } else
    {
      header("Location:/?error=$error_msg");
    }
  }

  public static function sign_in()
  {
    $error = false;
    $get_user = $username = $password = $stored_password = $success_msg = $error_msg = '';

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sign-in']))
    {
      if (empty($_POST['username']))
      {
        $error = true;
        $error_msg .= 'username cannot be empty';
      } else
      {
        $username = $_POST['username'];
      }

      if (empty($_POST['password']))
      {
        $error = true;
        $error_msg .= 'password cannot be empty';
      } else
      {
        $password = $_POST['password'];
      }

      if ($error === false)
      {
        $get_user = (new SignModel())->get_user($username);

        if ($get_user === false)
        {
          $error_msg .= 'user doesn\'t exist';
          header("Location:/?error=$error_msg");
        } else
        {
          $stored_password = $get_user['pass'];

          if (password_verify($password, $stored_password))
          {
            $user_id = $get_user['id'];
            $username = $get_user['name'];
            $folder_name = (new SignModel())->get_folder($user_id);

            $_SESSION['user'] = $username;
            $_SESSION['folder'] = $folder_name;
            $_SESSION['logged_in'] = true;

            $success_msg .= 'connexion successful!';
            header("Location:/folder?success=$success_msg");
          } else
          {
            $error_msg .= 'password incorrect';
            header("Location:/?error=$error_msg");
          }
        }
      } else
      {
        header("Location:/?error=$error_msg");
      }
    }
  }

  public static function sign_out()
  {
    if (($_SERVER['REQUEST_METHOD'] == 'GET') && isset($_GET['signout']))
    {
      session_unset();
      session_destroy();
      header('Location:/');
    }
  }
}