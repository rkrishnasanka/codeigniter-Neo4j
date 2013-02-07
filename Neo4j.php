<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//use Everyman\Neo4j\Query\Row;

class Neo4j
{
    public function __construct()
    {
        spl_autoload_register(array($this,'autoload'));        
        $this->Client = new Neo4j\Client();
 
	}

    function recursiveLoad($dir)
    {
        if (is_dir($dir)) 
        {
            if ($dh = opendir($dir)) 
            {
                while (($file = readdir($dh)) !== false) 
                {
                    if ($file != "." && $file != "..")
                    {
                        if(is_dir($subdir = str_replace('\\',DIRECTORY_SEPARATOR,$dir.DIRECTORY_SEPARATOR.$file) ))
                        {
                            $this->recursiveLoad($subdir);
                        }
                        else
                        {
                            $dirtypath = $dir . DIRECTORY_SEPARATOR . $file ;

                            $cleanpath = str_replace('\\',DIRECTORY_SEPARATOR,$dirtypath);
                            
                            require($cleanpath);
                            echo $cleanpath. '<br />';
                        }
                    }
                }
                closedir($dh);
            }
        }
    }
    
    public function autoload($sClass)
    {
        
        $sLibPath = __DIR__ . DIRECTORY_SEPARATOR;
       
        $sClassFile = str_replace('\\',DIRECTORY_SEPARATOR,$sClass).'.php';
        $sClassPath = $sLibPath.$sClassFile;
        
        
        if(file_exists($sClassPath))
        {
            require($sClassPath);
        }
        
        // Recursively load all subdirectories if the folder exists
        $sFolderPath = str_replace('\\',DIRECTORY_SEPARATOR,$sClass);

        if (is_dir($sFolderPath)) 
        {
            $this->recursiveLoad($sFolderPath);
        }
    }
    
    public function Cypher($queryString)
    {
        $query = new Neo4j\Cypher\Query($this->Client, $queryString);
        //$result = $query->getResultSet();

        return $query;
    }
}

?>