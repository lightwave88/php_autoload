<?php

namespace system\lib;

class Autoload {

  protected static $instance = null;
  protected static $namespaceMap = [];
  protected static $separaotr = null;
  // 要有 / 做結尾
  protected static $roorDir = null;
  protected static $newLine;

  const LTRIM = '/^(\\\\|\\/)+/';
  const RTRIM = '/(\\\\|\\/)+$/';
  const REG_2 = '/^(.*?)\\\\/';

  //--------------------------------------
  public static function init($rootPath) {

    if (!is_null($rootPath)) {
      static::setRootDir($rootPath);
    }
    if (is_null(static::$instance)) {
      $obj = static::$instance = new Static();
      spl_autoload_register(array($obj, '_callback'));
    }

    static::$newLine = is_null($_SERVER['SERVER_NAME']) ? PHP_EOL : '<br>';
  }

  //--------------------------------------
  public static function addDirs($namespace, $dir) {

    if (gettype($dir) != 'string') {
      $dir = '';
    } else {
      $dir = trim($dir);
    }

    $namespace = trim($namespace, '\\/');
    $dirs = [];

    if (is_array($namespace)) {
      $dirs = $namespace;
    } else {
      $dirs[$namespace] = $dir;
    }

    foreach ($dirs as $_namespace => $_dir) {
      static::$namespaceMap[$_namespace] = static::_checkPath($_dir);
    }
  }

  //--------------------------------------
  public static function setRootDir($dir) {
    $dir = trim($dir, '\\/');

    if (strlen($dir) > 0) {
      static::$roorDir = $dir;
    }
  }

  //--------------------------------------
  protected function _callback($class) {

    printf('class = %s%s', $class, static::$newLine);

    $this->_loadClassFile($class);

    if (!class_exists($class)) {
      throw new \Exception("class($class) no exits");
    }
  }

  //--------------------------------------
  protected function _loadClassFile($class) {
    $separator = DIRECTORY_SEPARATOR;
    $pathList = [];

    $root = static::$roorDir;
    if (strlen($root) > 0) {
      $root .= $separator;
    }
    //------------------
    // 註冊表中是否有
    if (preg_match(static::REG_2, $class, $matches)) {
      $dir = $matches[1];
      $path = preg_replace(static::REG_2, '', $class);

      if (key_exists($dir, static::$namespaceMap)) {
        // 有設定特殊的 namespace->dir
        $dir = static::$namespaceMap[$dir];

        if (strlen($dir) > 0) {
          $dir .= $separator;
        }
        //-------------
        $path_1 = sprintf('%s%s%s.php', $root, $dir, $path);
        if ($this->_loadFile($path_1)) {
          return;
        } else {
          $pathList[] = $path_1;
        }
        //-------------
        /*
          $path_1 = sprintf('%s%s%s/%s.php', $root, $dir, $path, $path);
          if ($this->_loadFile($path_1)) {
          return;
          } else {
          $pathList[] = $path_1;
          }
         */
      }
    } // if
    //--------------
    $path_1 = sprintf('%s%s.php', $root, $class);
    if ($this->_loadFile($path_1)) {
      return;
    } else {
      $pathList[] = $path;
    }
    /*
      $path_1 = sprintf('%s%s/%s.php', $root, $class, $class);
      if ($this->_loadFile($path_1)) {
      return;
      } else {
      $pathList[] = $path;
      }
     */
    $pathList = implode('|', $pathList);
    throw new \Exception("class($class) file($pathList) no exists");
  }

  //--------------------------------------
  protected function _loadFile($file) {

    printf('filePath = %s%s', $file, static::$newLine);

    if (file_exists($file)) {
      require_once($file);
      return true;
    }
    return false;
  }

  //--------------------------------------
  protected static function _checkPath($_path) {

    $_path = trim($_path);
    $_path = rtrim($_path, '\\/');

    $replace = static::_getSeparator();
    $separator = DIRECTORY_SEPARATOR;

    $reg_2 = "/\\$replace/";

    $path = preg_replace($reg_2, $separator, $_path);
    return $path;
  }

  //--------------------------------------
  protected static function _getSeparator() {
    if (is_null(static::$separaotr)) {
      $separator = DIRECTORY_SEPARATOR;
      switch ($separator) {
        case '\\':
          static::$separaotr = '/';
          break;
        default:
          static::$separaotr = '\\';
          break;
      }
    }
    return static::$separaotr;
  }

}

/*
example:

Autoload::init();

// 設定專案根目錄
Autoload::setRootDir('test_1');

// 指定 namespace 映射的路徑(相對根目錄)
Autoload::addDirs('sys', 'lib');

// namespace \root 的目錄
Autoload::addDirs('root', '');

new \root\X();
new \sys\db\Database();
new \Y();

new \g\Y();
 */
