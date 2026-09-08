<?php

namespace Tests\Feature\Validation;

use App\Support\Validation\InputNormalizer;
use App\Support\Validation\InputRules;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class InputRulesTest extends TestCase
{
    public static function formats(): iterable
    {
        $cases = [
            'person' => [
                [true, ['Vicente', 'José Luis', 'María José', 'Ana-María', "O'Connor", 'Álvarez', 'Muñoz', 'Peña', 'José Ángel', 'D’Angelo', "Jose\u{0301}"]],
                [false, ['Vicente123', '123456', '@Vicente', 'Vicente!!!', '??Vicente', '', 'A', str_repeat('a', 121), ['José']]],
            ],
            'email' => [
                [true, ['usuario@example.com', 'usuario.nombre+prueba@example.com']],
                [false, ['usuario', 'usuario@', '@example.com', 'usuario ejemplo@example.com', 'vicente@gmail', str_repeat('a', 255).'@example.com', ['a@example.com']]],
            ],
            'document' => [
                [true, ['8765432', '1234567-1A LP', 'AB12345', '1234567/LP']],
                [false, ['abcdef', '@@@@', '12', '1234!!!!', '1234  LP', str_repeat('1', 31), 1234567]],
            ],
            'phone' => [
                [true, ['77712345', '+59177712345', '1234567', '+123456789012345']],
                [false, ['abcdef', '77777777!!!!', '+591abc', '+1234567890123456', '123456', '++59177712345', 77712345]],
            ],
            'code' => [
                [true, ['FIS-001', 'GRUPO_A', 'AB12']],
                [false, ['fis-001', 'AB 12', 'AB!!', 'AB--12', '_AB', 'AB-', 'A', str_repeat('A', 61)]],
            ],
        ];
        foreach ($cases as $format => $groups) {
            foreach ($groups as [$valid, $values]) {
                foreach ($values as $index => $value) {
                    yield $format.'-'.($valid ? 'valid' : 'invalid').'-'.$index => [$format, $value, $valid];
                }
            }
        }
    }

    #[DataProvider('formats')]
    public function test_shared_formats(string $format, mixed $value, bool $valid): void
    {
        $validator = Validator::make(['value' => $value], ['value' => ['bail', 'required', ...InputRules::$format()]]);
        $this->assertSame($valid, $validator->passes(), $validator->errors()->toJson());
    }

    public function test_normalization_is_recursive_idempotent_and_preserves_credentials_and_long_text(): void
    {
        $input = [
            'nombres_post' => "  José    Ángel \t", 'email' => '  Usuario+Prueba@GMAIL.COM ',
            'codigo_prog' => ' fis-001 ', 'celular_post' => '+591 (777)-12345', 'ci_post' => '   ',
            'observaciones_post' => "  Primera  línea\n  Segunda línea  ",
            'password' => '  Password  ', 'current_password' => ' actual ', 'password_confirmation' => '  Password  ', 'token' => ' token ',
            'alternativas' => [['texto_alt' => "  A  B\nC  "]],
        ];
        $normalized = InputNormalizer::normalize($input);
        $this->assertSame('José Ángel', $normalized['nombres_post']);
        $this->assertSame('usuario+prueba@gmail.com', $normalized['email']);
        $this->assertSame('"foo  bar"@example.com', InputNormalizer::normalize(['email' => ' "Foo  Bar"@EXAMPLE.COM '])['email']);
        $this->assertSame('FIS-001', $normalized['codigo_prog']);
        $this->assertSame('+59177712345', $normalized['celular_post']);
        $this->assertNull($normalized['ci_post']);
        $this->assertSame("Primera  línea\n  Segunda línea", $normalized['observaciones_post']);
        $this->assertSame("A  B\nC", $normalized['alternativas'][0]['texto_alt']);
        foreach (['password', 'current_password', 'password_confirmation', 'token'] as $field) {
            $this->assertSame($input[$field], $normalized[$field]);
        }
        $this->assertSame($normalized, InputNormalizer::normalize($normalized));
        $this->assertSame(['sesion_asist' => ['bad']], InputNormalizer::normalize(['sesion_asist' => ['bad']]));
    }

    public static function percentages(): iterable
    {
        foreach ([0, 50, 100, '10.50'] as $value) {
            yield 'valid-'.$value => [$value, true];
        }
        foreach ([-1, 101, 500, 'abc', '1.001'] as $value) {
            yield 'invalid-'.$value => [$value, false];
        }
    }

    #[DataProvider('percentages')]
    public function test_percentage_boundaries(mixed $value, bool $valid): void
    {
        $this->assertSame($valid, Validator::make(['value' => $value], ['value' => ['bail', 'required', 'numeric', 'decimal:0,2', 'min:0', 'max:100']])->passes());
    }

    public function test_password_confirmation_minimum_and_bcrypt_byte_limit(): void
    {
        foreach (['password', 'JoséÁngel123', str_repeat('a', 72)] as $password) {
            $this->assertTrue(Validator::make(compact('password') + ['password_confirmation' => $password], ['password' => ['bail', 'required', ...InputRules::password()]])->passes());
        }
        foreach (['short', str_repeat('a', 73), str_repeat('ñ', 37), ['password']] as $password) {
            $this->assertFalse(Validator::make(compact('password') + ['password_confirmation' => $password], ['password' => ['bail', 'required', ...InputRules::password()]])->passes());
        }
        $this->assertFalse(Validator::make(['password' => 'password', 'password_confirmation' => 'different'], ['password' => InputRules::password()])->passes());
    }
}
