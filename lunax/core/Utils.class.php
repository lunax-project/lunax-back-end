<?php
/**
 * Utilitaries useds on application
 */
class Utils
{
    /**
     * Gets application name in configuration
     * @return String
     */
    public static function appName()
    {
        return self::escape($GLOBALS['app']->getConfig('name'));
    }

    /**
     * Function equivalent NOW() on MySql
     * @return String Current datetime
     */
    public static function now()
    {
        return date("Y-m-d H:i:s");
    }

    /**
     * Substitue os caracteres especiais do HTML
     * @param  String $str
     * @return String
     */
    public static function escape($str)
    {
        return htmlspecialchars($str);
    }

    /**
     * Substitue os caracteres especiais, mais
     * permite a quebra de linha
     * @param  String $str
     * @return String
     */
    public static function escapeLong($str)
    {
        return nl2br(self::escape($str));
    }

    /**
     * Display error if configured
     * @param  String  $message Message to display
     * @param  boolean $break   break page if is true
     */
    public static function error(string $message, $break = false)
    {
        if ($GLOBALS['app']->getConfig('display_errors')) {
            self::log("ERROR: $message");
            echo self::escape($message);
        }

        # Force application break
        if ($break) exit;
    }

    /**
     * Save a log file with data
     * @param  String $message Message to save
     */
    public static function log($message)
    {
        if ($GLOBALS['app']->getConfig('save_log')) {
            # Prepare variables
            $time           = date('H:i:s');
            $date           = date("Y-m-d");
            $remoteAddr     = $_SERVER['REMOTE_ADDR'];

            # A ordem ano-mês-dia serve para organizar ficar mais fácil de localizar
            $filename = join(array(APPDIR, 'log', '$date.log'), DS);

            # Prepare data to make log
            $dataLog = "[$time] - $remoteAddr:" . PHP_EOL;
            $dataLog .= $message                . PHP_EOL;
            $dataLog .= PHP_EOL;

            # Write the contents to the file,
            # using the FILE_APPEND flag to append the content to the end of the file
            # and the LOCK_EX flag to prevent anyone else writing to the file at the same time
            file_put_contents($filename, $dataLog, FILE_APPEND | LOCK_EX);
        }
    }

    /**
     * Gera uma url completa baseada no que for passado
     * @param  String $url URL que será passada
     * @return String      URL completa
     */
    public static function getURL($url)
    {
        $separator      = '/';
        $appRequest = $GLOBALS['app']->request;
        $serverRoot     = preg_replace('/^(\/|\\\)/', '', $appRequest->getServerRoot());
        $parts          = array_filter(explode($separator, $url), 'strlen');
        $absolutes      = array($appRequest->getAbsoluteUrl());

        # Caso não esteja na raiz adiciona o diretório
        if (!empty($serverRoot)) {
            array_unshift($parts, $serverRoot);
        }

        foreach ($parts as $part) {

            if ('.' == $part) continue;

            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }

        }

        return implode($separator, $absolutes);
    }

    /**
     * Include a file with template
     * @param  String $fileName File with template
     * @return String           Content of file
     */
    public static function includeDataFile($fileName)
    {
        if (file_exists($fileName)) {
            # Start output buffer
            ob_start();

            include $fileName;

            # Get contents of buffer
            return ob_get_clean();
        } else {
            self::error("File \"$filename\" not found!");
        }
    }

    /**
     * Converte um número para valores de moeda
     * @param  Number $value Numero que será convertido
     * @return  String        Número após conversão
     */
    public static function parseValue($value)
    {
        return number_format($value, 2, ',', '.');
    }

    /**
     * Transforma os valores em porcentagem
     * @param  Number $lastValue Valor anterior
     * @param  Number $newValue  Valor atual
     * @return   String            Quantos por centos teve de desconto
     */
    public static function getPercentDiscount($lastValue, $newValue)
    {
        $parcentValue = ceil(($newValue * 100) / $lastValue);
        return 100 - $parcentValue . '%';
    }

    /**
     * Redirect browser to $url
     * @param  String $url
     */
    public function location($url)
    {
        # Redirect browser
        header("Location: $url");

        self::log("Location to $url");

        # Make sure that code below does not
        # get executed when we redirect.
        exit;
    }

    /**
     * Get data using method post
     * @param  String $url  URL to send post
     * @param  Array  $data Data of param
     * @return Mixed        Result of post
     */
    public function post($url, $data = null)
    {
        $cURL = curl_init($url);
        curl_setopt($cURL, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($cURL, CURLOPT_POST, TRUE);

        if (!is_null($data)) {
            curl_setopt($cURL, CURLOPT_POSTFIELDS, $data);
        }

        $result = curl_exec($cURL);
        curl_close($cURL);
        return simplexml_load_string($result);
    }
}
