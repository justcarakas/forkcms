<?php

declare(strict_types=1);
/**
 * @global string $class_name
 * @global string $namespace
 * @global string[] $useStatements
 */
?>
<?= "<?php\n\ndeclare(strict_types=1);\n"; ?>

namespace <?= $namespace ?>;

<?php foreach ($useStatements as $useStatement) echo $useStatement.PHP_EOL ?>

final class <?= $class_name, PHP_EOL ?>
{

}
