<?php

class Livro{
	private $id;
	private $titulo;
	private $editora;
	private $isbn;
	private $edicao;
	private $autor;

	function __construct($titulo, $editora, $isbn, $edicao, $autor)
	{
		$this->titulo = $titulo;
		$this->editora = $editora;
		$this->isbn = $isbn;
		$this->edicao = $edicao;
		$this->autor = $autor;
	}

	public function getId() {
		return $this->id;
	}
	public function setId($id) {
		$this->id = $id;
	}
	public function getTitulo() {
		return $this->titulo;
	}
	public function setTitulo($titulo) {
		$this->titulo = $titulo;
	}
	public function getEditora() {
		return $this->editora;
	}
	public function setEditora($editora) {
		$this->editora = $editora;
	}
	public function getEdicao() {
		return (int) $this->edicao;
	}
	public function setEdicao($edicao) {
		$this->edicao = $edicao;
	}
	public function getAutor() {
		return $this->autor;
	}
	public function setAutor($autor) {
		$this->autor = $autor;
	}

	public function getIsbn() {
		return (int) $this->isbn;
	}

	public function setIsbn($isbn) {
		$this->isbn = $isbn;
	}
}

class Sessao{
	public $livro;
}

class ControleSessao{
	public function iniciarSessao(){
		if (empty(session_id())) {
			session_start();
			if(!isset($_SESSION['sessao'])){
				$_SESSION['sessao'] = new Sessao;
			}
		}
	}

	public function fecharSessao(){
		$this->iniciarSessao();
		$_SESSION = array();
		session_destroy();
	}

	public function setLivro($livro){
		$this->iniciarSessao();
		$_SESSION['sessao']->livro = $livro;
	}

	public function getLivro(){
		$this->iniciarSessao();
		return $_SESSION['sessao']->livro;
	}
}

class GerenciarLivro{
	private $controleSessao;
	private $cliente;
	function __construct($cliente)
	{
		$this->cliente = $cliente;
		$this->controleSessao = new ControleSessao;
	}

	public function cadastrar($livro){
		$param = array(
			'titulo' => $livro->getTitulo(),
			'editora' => $livro->getEditora(),
			'isbn' => $livro->getIsbn(),
			'edicao' => $livro->getEdicao(),
			'autor' => $livro->getAutor()
		);
		$this->cliente->__soapCall('cadastrar', array($param));	
	}

	public function excluir(){
		$param = array(
			'livro' => $this->controleSessao->getLivro()
		);
		$this->cliente->__soapCall('excluir', array($param));
		$this->controleSessao->fecharSessao();
	}

	public function alterar(){
		$param = array(
			'livro' => $this->controleSessao->getLivro()
		);
		$this->cliente->__soapCall('alterar', array($param));
		$this->controleSessao->fecharSessao();
	}

	public function consultarISBN($isbn){
		$param = array(
			'isbn' => (int)$isbn
		);
		$livro = $this->gerarLivro(array_values((array) $this->cliente->__soapCall('consultarISBN', array($param))));
		$this->controleSessao->setLivro($livro);
	}

	public function gerarLivro($arrayLivro){
		$livro = new Livro(
				$arrayLivro[0]->titulo,
				$arrayLivro[0]->editora,
				$arrayLivro[0]->isbn,
				$arrayLivro[0]->edicao,
				$arrayLivro[0]->autor 
			);
		$livro->setId($arrayLivro[0]->id);
		return $livro;
	}	
}


#Controle

	$cliente = new SoapClient('http://localhost:9091/Livraria_Remota/services/GerenciarLivroWS?WSDL');
	$gerenciarLivro = new GerenciarLivro($cliente);
	$controleSessao = new ControleSessao;


	if(isset($_POST['cadastro'])){
		$livro = new Livro(
			$_POST['titulo'],
			$_POST['editora'],
			$_POST['isbn'],
			$_POST['edicao'],
			$_POST['autor']
		);
		$gerenciarLivro->cadastrar($livro);
	}

	elseif(isset($_POST['exclusao'])){
		$gerenciarLivro->excluir();
	}

	elseif(isset($_POST['alteracao'])){
		$livro = new Livro(
			$_POST['titulo'],
			$_POST['editora'],
			$_POST['isbn'],
			$_POST['edicao'],
			$_POST['autor']
		);
		$livro->setId((int)$_POST['id']);
		$controleSessao->setLivro($livro);
		$gerenciarLivro->alterar();
	}

	elseif(isset($_POST['pesquisa'])){
		$isbn = $_POST['isbn'];
		$gerenciarLivro->consultarISBN($isbn);
	}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Bare - Start Bootstrap Template</title>

    <!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
    body {
        padding-top: 70px;
        /* Required padding for .navbar-fixed-top. Remove if using .navbar-static-top. Change if height of navigation changes. */
    }
    </style>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body>

    <!-- Navigation -->
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
        <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#">Livraria</a>
            </div>
            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    <li class="nav-item">
                        <a id="tabCadastro" class="nav-link" href="#">Cadastro</a>
                    </li>
                </ul>

                <ul class="nav navbar-nav navbar-right">
                	<li>
                		<form action="#" method="post" style="margin-top:9px;" class="form-inline pull-xs-right ">
						    <input name="isbn" class="form-control" type="text" placeholder="Pesquisar livro por ISBN">
						    <input id="tabPesquisa" class="btn btn-outline-success" type="submit" name="pesquisa" value="Pesquisar">
						</form>
                	</li>
                </ul>
			 
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container -->
    </nav>

    <!-- Page Content -->
    <div class="container">

    	<div id="cadastro">

		    <div>
			    <form action="#" method="post">
			    	<div class="row">
		        		<label class="col-xs-12 col-sm-1" for="titulo">Titulo:</label>
						<input class="col-xs-12 col-sm-2" type="text" name="titulo" id="titulo">
			        </div>
		        	<div class="row">
		        		<label class="col-xs-12 col-sm-1" for="editora">Editora:</label>
						<input class="col-xs-12 col-sm-2" type="text" name="editora" id="editora">
					</div>
					<div class="row">
						<label class="col-xs-12 col-sm-1" for="autor">Autor:</label>
						<input class="col-xs-12 col-sm-2" type="text" name="autor" id="autor">
					</div>
					<div class="row">
						<label class="col-xs-12 col-sm-1" for="isbn">ISBN:</label>
						<input class="col-xs-12 col-sm-2" type="text" name="isbn" id="isbn">						
					</div>
					<div class="row">
						<label class="col-xs-12 col-sm-1" for="Edição">Edição:</label>
						<input class="col-xs-12 col-sm-2" type="text" name="edicao" id="edicao">					
					</div>		
					<div class="row">						
						<input class="col-sm-1" type="submit" name="cadastro" value="Cadastrar">
			    	</div>
				</form>
			    <!-- /.row -->
			</div>

		</div>

		<?php 
			if(empty(session_id()))session_start();
			if(isset($_SESSION['sessao']) && !is_null($_SESSION['sessao']->livro)): 
		?>
			<div id="pesquisa">	
				<div class="form-group">
				    <form action="#" method="post">
				    	<input type="hidden" name="id" value=<?php echo $_SESSION['sessao']->livro->getId() ?> />
				    	<div class="row">
			        		<label class="col-xs-12 col-sm-1" for="tituloPesquisa">Titulo:</label>
							<input class="col-xs-12 col-sm-2" type="text" name="titulo" id="tituloPesquisa"
							value=<?php echo $_SESSION['sessao']->livro->getTitulo() ?>>
				        </div>
			        	<div class="row">
			        		<label class="col-xs-12 col-sm-1" for="editoraPesquisa">Editora:</label>
							<input class="col-xs-12 col-sm-2" type="text" name="editora" id="editoraPesquisa"
							value=<?php echo $_SESSION['sessao']->livro->getEditora() ?>>
						</div>
						<div class="row">
							<label class="col-xs-12 col-sm-1" for="autorPesquisa">Autor:</label>
							<input class="col-xs-12 col-sm-2" type="text" name="autor" id="autorPesquisa"
							value=<?php echo $_SESSION['sessao']->livro->getAutor() ?>>
						</div>
						<div class="row">
							<label class="col-xs-12 col-sm-1" for="isbnPesquisa">ISBN:</label>
							<input class="col-xs-12 col-sm-2" type="text" name="isbn" id="isbnPesquisa"
							value=<?php echo $_SESSION['sessao']->livro->getIsbn() ?>>						
						</div>
						<div class="row">
							<label class="col-xs-12 col-sm-1" for="edicaoPesquisa">Edição:</label>
							<input class="col-xs-12 col-sm-2" type="text" name="edicao" id="edicaoPesquisa"
							value=<?php echo $_SESSION['sessao']->livro->getEdicao() ?>>					
						</div>		
						<div class="row">						
							<input class="col-sm-1" type="submit" name="alteracao" value="Alterar">
							<input class="col-sm-1" type="submit" name="exclusao" value="Excluir">
				    	</div>
					</form>
				    <!-- /.row -->
				</div>

			</div>
		<?php endif; ?>

    </div>
    <!-- /.container -->

    <!-- jQuery Version 1.11.1 -->
    <script src="js/jquery.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.min.js"></script>

    <script type="text/javascript">
    	
    	$( "#cadastro" ).hide();

	 	$("#tabCadastro").click(function(e){
			$( "#cadastro" ).show();
			$( "#pesquisa" ).hide();
		});

		$("#tabPesquisa").click(function(e){
			$( "#pesquisa" ).show();
			$( "#cadastro" ).hide();
		});

    </script>

</body>

</html>
