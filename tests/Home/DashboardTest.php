<?php declare(strict_types=1);

namespace Tests\Home;

use PHPUnit\Framework\TestCase;
use App\Controllers\Home\Dashboard;

final class DashboardTest extends TestCase 
{   

    public function testContent(): void
    {
        //Arrange --------------->
        $home = new Dashboard(); 
        $arrange = $home->printContent("ok");

        //Act ------------------->
        $act = "<p>ok</p>";

        //assert --------------->
        if($arrange !== "" && $arrange === $act){
            echo "Teste Passou!" . PHP_EOL;
        }else{
            echo "Teste Não Passou!" . PHP_EOL;
        }       

        $this->assertNotEmpty($arrange);
        $this->assertSame($arrange, $act);
    }   

    public function testHead(): void
    {
        //Arrange --------------->
        $home = new Dashboard(); 
        $arrange = $home->printHead("ok");

        //Act ------------------->
        $act = "<p>ok</p>";

        //assert --------------->
        if($arrange === $act){
            echo "Teste Passou!" . PHP_EOL;
        }else{
            echo "Teste Não Passou!" . PHP_EOL;
        }

        $this->assertSame($arrange, $act);
    }

    public function testBody(): void
    {       
        //Arrange --------------->
        $home = new Dashboard(); 
        $arrange = $home->printBody("ok");

        //Act ------------------->
        $act = "<p>ok</p>";

        //assert --------------->
        if($arrange === $act){
            echo "Teste Passou!" . PHP_EOL;
        }else{
            echo "Teste Não Passou!" . PHP_EOL;
        }

        $this->assertSame($arrange, $act);
    }

    public function testFooter(): void
    {
        //Arrange --------------->
        $home = new Dashboard(); 
        $arrange = $home->printFooter("ok");

        //Act ------------------->
        $act = "<p>ok</p>";

        //assert --------------->
        if($arrange === $act){
            echo "Teste Passou!" . PHP_EOL;
        }else{
            echo "Teste Não Passou!" . PHP_EOL;
        }

        $this->assertSame($arrange, $act);
    }
}