<?php 
namespace App\Services;

use Exception;
use Symfony\Component\Process\Process;
class ServerSocketService{
    
    private $production = null;
    private $port = null;
    private $cliente = null;
    
    function __construct($cliente = null, $production = false,$port = 3000){
        $this->production = $production;
        $this->port = $port;
        $this->cliente = is_null($cliente) ? uniqid() : $cliente;
    }

    private function getConfig($production = false, $port){

        $prod = $production ? 'true' : 'false';

        return "module.exports = config = {
            production:{$prod},
            port: {$port},
            key: './certificados_ssl/certificado.key',
            cert: './certificados_ssl/certificado.crt'
        }";
    }


    private function getFolder() : string{

        $folder = base_path('socket\\'.$this->cliente);
        
        if(!is_dir($folder)) mkdir($folder);

        return $folder;

    }

    private function getConfigFile(){
        return $this->getFolder().'\\config.js';
    }

    private function getIndexFile(){
        return $this->getFolder().'\\index.js';
    }

    private function existFile(){
        return file_exists($this->getIndexFile());
    }

    private function create(){
        $indexFile = base_path('socket\\index.js.base');
        $toIndex = $this->getIndexFile();
        if(copy($indexFile, $toIndex)){
            return true;
        }
        throw new Exception('No se pudo crear el archivo');
    }

    public function run(){
        
        $this->setConfig($this->port, $this->production);

        $this->create();

        $this->start();
    }

    public function setConfig($port, $production = false ){
        $to = $this->getConfigFile();
        $fp = fopen($to, 'w');
        fwrite($fp, $this->getConfig($production, $port));
        fclose($fp);
    }

    public function start(){
        $toIndex = $this->getIndexFile();
        $process = new Process("pm2 start {$toIndex} --name={$this->cliente}");
        $process->run();
    }

    public function stop(){
        $process = new Process("pm2 stop {$this->cliente}");
        $process->run();
    }

    public function restart(){
        $this->stop();

        if(!$this->existFile()){
            $this->create();
        }
        
        $this->start();
    }

    public function destroy(){
        $stopProcess = new Process("pm2 delete {$this->cliente}");
        $stopProcess->run();
    }




}