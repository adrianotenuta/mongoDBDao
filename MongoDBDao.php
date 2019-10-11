<?php

/**
 * Classe para acesso e controle básico de um banco de dados em MongoDB
 * Operações:[select, insert, delete]
 * Este script foi criado na versao 7.4 do PHP, para versões anteriores será necessário adaptação
 * @version 1.0
 * @author Adriano Tenuta
 */
class MongoDBDao
{
    private object $manager;
    private object $bulkWrite;
    private string $host = "localhost";
    private string $port = "27017";
    private string $dataBase = 'dataBaaseName';
    private string $collation = 'collationName';
    private array $dataCollaction = [];
    private object $queryFilter;

    public function __construct()
    {
        $this->manager = new MongoDB\Driver\Manager("mongodb://" . $this->host . ":" . $this->port);
        $this->bulkWrite = new MongoDB\Driver\BulkWrite;
    }

    /**
     * Atribui o conjunto de dados que será inserido na collation (tabela)
     * @param array $dataCollation
     */
    public function setDataCollation(array $dataCollation)
    {
        $this->dataCollaction = $dataCollation;
    }

    /**
     * Atribui o nome de outra collation (tabela) para consulta
     * @param string $name
     */
    public function setCollation(string $name)
    {
        $this->collation = $name;
    }

    /**
     * Recupera o nome da collation (tabela) atual
     * @return string
     */
    public function getCollation()
    {
        return $this->collation;
    }

    /**
     * Cria filtro para consulta de um registro. Ex.: ['id_cliente' => 2851]
     * @param array $filter
     */
    public function setQueryFilter(array $filter = [])
    {
        $this->queryFilter = new MongoDB\Driver\Query($filter);
    }

    //------------------------------------------------------------------------------------------------------------------

    /**
     * Recupera a coleção desejada confome os parametros de consulta
     * EXEMPLO DE USO:
     * $mongoDB->setQueryFilter(['_id' => new MongoDB\BSON\ObjectID("5d829a26ad4700000a001639")]);
     * $queryResult = $mongoDB->select();
     * @return object|Exception
     * @throws \MongoDB\Driver\Exception\Exception
     * @author Adriano Tenuta
     */
    public function select()
    {
        try {
            if (empty($this->queryFilter)) throw new Exception('Para consultar registros no mongo, é necessário enviar o array contendo os filtros desejados.');

            $result = $this->manager->executeQuery($this->dataBase . '.' . $this->collation, $this->queryFilter);
            if ($result instanceof \MongoDB\Driver\Exception\Exception) throw $result;

            $dsResult = [];
            foreach ($result as $resultItens) $dsResult = json_decode(json_encode($resultItens), true);

            return $dsResult;
        } catch (Exception $e) {
            return $e;
        }
    }

    /**
     * Insere um novo objeto e retorna o ID gerado automaticamente
     * EXEMPLO DE USO:
     * $mongoDB->setDataCollation(['id_cliente' => 2851, 'nome' => 'RB Serviços', 'data' => date('Y-m-d'), 'contatos' => ['id' => 123, 'nome' => 'João da Silva', 'telefone' => '11958896647']]);
     * $id = $mongoDB->insert();
     * @return Exception|string
     * @author Adriano Tenuta
     */
    public function insert()
    {
        try {
            if (count($this->dataCollaction) <= 0) throw new Exception('Para inserir dados no mongo, é necessário enviar o array contendo os dados desejados.');

            $id = $this->bulkWrite->insert($this->dataCollaction);
            $this->manager->executeBulkWrite($this->dataBase . '.' . $this->collation, $this->bulkWrite);

            return (string)$id;
        } catch (Exception $e) {
            return $e;
        }
    }

    /**
     * Deleta permanentemente um regitro da collation(tabela)
     * EXEMPLO DE USO:
     * $mongoDB->setQueryFilter(['_id' => new MongoDB\BSON\ObjectID("5d829a26ad4700000a001639")]);
     * $deletedItem = $mongoDB->delete();
     * @return bool|Exception
     * @author Adriano Tenuta
     */
    public function delete()
    {
        try {
            if (empty($this->queryFilter)) throw new Exception('Para deletar registros no mongo, é necessário enviar o array contendo os filtros desejados.');

            $this->bulkWrite->delete($this->queryFilter, ['limit' => 1]);
            $this->manager->executeBulkWrite($this->dataBase . '.' . $this->collation, $this->bulkWrite);

            return true;
        } catch (Exception $e) {
            return $e;
        }
    }
}

//Exemplo de consulta
$mongoDB = new MongoDBDao();
$mongoDB->setCollation('nome_tabela');
$mongoDB->setQueryFilter(['_id' => new MongoDB\BSON\ObjectID("5ds5d6as65da6s5da6s8")]);
$queryResult = $mongoDB->select();

var_dump($queryResult);