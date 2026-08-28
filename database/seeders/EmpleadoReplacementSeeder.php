<?php

namespace Database\Seeders;

use App\Models\Sucursal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class EmpleadoReplacementSeeder extends Seeder
{
    /**
     * Dataset exacto de empleados.
     *
     * Cada elemento: [puesto (área), nombre completo, horario, fecha nacimiento,
     * números de emergencias, tipo sangre, enfermedad, alergias, medicamento,
     * dirección, teléfono personal, CURP].
     */
    private function empleados(): array
    {
        return [
            ['Dirección General', 'Paulina Pulido Cecaldi', 'NA', '1979-10-08', 'Manuel 55-5403-6186/ Hermano Hector 442-281-2367/Hermana Rosa Maria 442-219-0297/ CASA 55.9129.6720/ Casa Papas 442-589-7957', 'OH+', 'Ninguna', 'Acido Acetil', 'Ninguno', 'Privada Acueducto 22 Col. Bosque Real C.P. 52774', '55-5403-6188', null],
            ['Dirección General', 'Lorena Perez Lemmen Meyer', 'NA', '1982-07-10', '553-988-3045 (Esposo) 552-737-4151 (Hermana) 555-408-4192 (Papá)', 'A-', 'Ninguna', 'Sulfa', 'Ninguno', 'Real de acueducto casa número 4 Colonia bosque real Huixquilucan Estado de México Cp 52774', '55-5419-7297', null],
            ['Coordinación Escolar', 'Patricia Prado Garcia', 'Lunes a Viernes 8:00 - 17:00', '1974-05-07', 'Sobrino Gustavo Barragan 55 6322 6950', 'O+', 'Migraña ocasional, Demensia temporal', 'Ninguna', 'Quetiapina, sertralina', 'Calle San Antonio manz 512 lote 10 col santa Úrsula Coapa cp04600 alcaldía Coyoacán', '55 5917 2484', null],
            ['Maestras', 'Marisol Quiroz Villanueva', 'Lunes a Viernes 8:00 - 14:30', '1984-10-16', '55-4788-2896 Margarita (mamá)', 'O+', 'Ninguna', 'Ninguna', 'Ninguno', 'Calle Tarango #12 col ampliacion las aguilas cp.01710', '55-3664-8489', 'QUVM841016MDFRLR00'],
            ['Maestras', 'Andrea Viridiana Moreno Nateras', 'Lunes a Viernes 8:00 - 14:30', '1984-11-10', '55-5195-1358 Papá y 55-5668-4756 Casa', 'O+', 'Reflujo', 'Ninguna', 'omeoptraxol', 'morelos N16 san jeronimo lirise', '55-1008-4689', null],
            ['Maestras', 'Brenda Osiris Morales Torres', 'Lunes a Viernes 8:00 - 14:30', '1977-02-17', '55-6525-0178 Mamá', 'A+', 'Ninguna', 'Ninguna', 'Ninguno', 'Av. 16 de septiembre #58 col. Pasteros', '55-3717-8995', 'MOTB770217MMDFRRR'],
            ['Maestras', 'Maria Eloina Carriles Bretón', 'Lunes a Viernes 8:00 - 14:30', '1974-05-11', '55 4477 5086 (hija)', 'O+', 'Ninguna', 'Ninguna', 'Ninguno', 'Hacienda de las golondrinas num.25 ph2', '55-2301-1843', 'CABE740511MPLRRL05'],
            ['Maestras', 'Frida Sarahi Pacheco Musiño', 'Lunes a Viernes 8:00 - 14:30', '2001-03-25', '55-2492-8322 Papá', 'A+', 'Ninguna', 'Ninguna', 'Ninguno', 'Av. Iztacalco num 78 Col agricola pantitlan delegacion iztacalco', '55-6222-5492', 'PAMF010325MDFCCRA5'],
            ['Maestras', 'Daniela Michell Godinez Belmont', 'Lunes a Viernes 8:00 - 14:30', '1998-04-30', '56-1277-0524 Mamá', 'O+', 'Ninguna', 'Ninguna', 'Ninguno', 'Carretera santa rosa san mateo num 12 col santa rosa Xochiac', '55-8368-3654', 'GOBD980430MDFDLN15'],
            ['Maestras', 'Veronica Alejandra Cordero Palacios', 'Lunes a Viernes 8:00 - 14:30', '1970-01-11', '55-7516-5188 Esposo', 'O+', 'Hipertension, Hipotiroidismo', 'Miel', 'Eutirox y Captopril', 'av. Toluca 1133 col olivar de los padres', '55-2105-5261', null],
            ['Maestras', 'Evelyn Morales Velázquez', 'Lunes a Viernes 8:00 - 14:30', '1981-08-19', '55-3196-7088 Hermana', 'O+', 'Ninguna', 'Ninguna', 'Ninguno', 'Zacamulpa Num 5 san Bartolo Ameyalco', '56-1546-7388', 'MOBE810819MDFRLV06'],
            ['Auxiliares', 'Leslie Flores Meza', 'Lunes a Viernes 8:00 - 16:00', '2001-02-19', '55-8316-2477 mamá', 'O-', 'Ninguna', 'Ninguna', 'Ninguno', 'calle malinche num. 30 col la malinche', '55-3198-5727', null],
            ['Auxiliares', 'Aidee Galicia Jimenez', 'Lunes a Viernes 8:00 - 16:00', '1995-07-13', '55-3864-8127 esposo', 'O+', 'Ninguna', 'Ninguna', 'Ninguno', 'calzada desierto de los leones km 24 1/2 num.7600', '55-7085-6507', null],
            ['Auxiliares', 'Blanca Rocio Pineda Perez', 'Lunes a Viernes 8:00 - 16:00', '1995-10-24', '55-8140-9740 Noemi (hermana) 55-2739-0410 Cesar mondragon (novio)', 'O+', 'Ninguna', 'Ninguna', 'Ninguno', 'calle almorejos lt 73 mza 9 col san clemente cp 01740', '55-4985-6686', null],
            ['Auxiliares', 'Leslie Mariana Cruz Garcia', 'Lunes a Viernes 8:00 - 16:00', '2004-05-20', '55-9318-2203 casa', 'O+', 'Ninguna', 'Ninguna', 'Ninguno', 'Magnolias num 7 ocotillos del pueblo tetelpan cp 01700', '55-2081-9004', 'CUGL040520MDFRRSA3'],
            ['Auxiliares', 'Itzel Marilin Gómez Martinez', 'Lunes a Viernes 8:00 - 16:00', '1993-04-22', '55-5403-1033 hermana', 'O+', 'Ninguna', 'Esteroides', 'Ninguno', 'Calzada desierto de los leones km 28.5 col. Santa rosa xochiac delegacion cuajimalpa', '55-4549-7082', 'GOMI930422MDFMRT09'],
            ['Futbol', 'Ricardo Fuentes Rodriguez', 'Lunes a jueves 9:00 - 15:00', '1997-07-13', '55-3736-0427 Mamá', 'O+', 'Ninguna', 'Ninguna', 'Ninguno', 'Calle chabacano mza 12 lote 26 col. Lomas de chamontoya', '55-5274-3998', 'FURR970713HDFNDC0'],
            ['Musica', 'Jaime Emilio Alejo Flores', 'Jueves 9:00-2:00', '1968-02-17', '55-1951-2752 Bertha', 'O+', 'Ninguna', 'Ninguna', 'Salbutalmon', 'Av. Mexico numero 430 jesus el monte cuajimalpa', '55-5498-8516', 'AEFJ680217HMNLLM01'],
            ['Administración', 'Ma. Viridiana Gómez Fierros', 'Lunes a Viernes 8:00 - 15:00', '1984-05-22', '55-6781-4472 Pareja', 'O+', 'Ninguna', 'Ninguna', 'Ninguno', 'Cedro num8 colonia el molino tezonco, Iztapalapa', '442-569-3051', 'GOFV840522MJC00'],
            ['Mantenimiento', 'Angel Nuñez Lopez', 'Lunes a Viernes 7:00 - 18:00', '1999-01-15', '55-7383-8728 Mamá y 55-1437-3915 Hermano', 'O+', 'Ninguna', 'Ninguna', 'Ninguno', 'Calle Fresnotitla N33-3 colonia tetelpan', '55-5800-2174', 'NULA990115HDFXPN04'],
        ];
    }

    public function run(): void
    {
        DB::transaction(function () {
            $sucursal = $this->defaultSucursal();

            // Desvincula a todos los usuarios de cualquier empleado previo.
            DB::table('users')->whereNotNull('empleado_id')->update(['empleado_id' => null]);

            // Reemplaza idempotentemente el listado completo de empleados.
            DB::table('empleados')->delete();

            $now = now();
            $rows = array_map(fn (array $e) => [
                'sucursal_id' => $sucursal->id,
                'nombre' => $this->nombre($e[1]),
                'apellido_paterno' => $this->apellidoPaterno($e[1]),
                'apellido_materno' => $this->apellidoMaterno($e[1]),
                'email' => null,
                'telefono' => null,
                'puesto' => $e[0],
                'horario' => $e[2],
                'fecha_nacimiento' => $e[3],
                'numeros_emergencia' => $e[4],
                'tipo_sangre' => $e[5],
                'enfermedad' => $e[6],
                'alergias' => $e[7],
                'medicamento' => $e[8],
                'direccion' => $e[9],
                'telefono_personal' => $e[10],
                'curp' => $e[11],
                'estatus_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ], $this->empleados());

            DB::table('empleados')->insert($rows);

            // Vincula exclusivamente al usuario viri con Ma. Viridiana Gómez Fierros.
            $this->vincularViri();
        });
    }

    /**
     * Busca una sucursal válida default/activa. Prioriza "Centro" o "Default";
     * si no existe, usa la primera activa. Falla si no hay ninguna sucursal
     * porque empleados requiere una sucursal obligatoria.
     */
    private function defaultSucursal(): Sucursal
    {
        $sucursal = Sucursal::active()
            ->where(function ($q) {
                $q->whereRaw('LOWER(nombre) LIKE ?', ['%centro%'])
                    ->orWhere('nombre', 'Default');
            })
            ->orderBy('id')
            ->first();

        if ($sucursal === null) {
            $sucursal = Sucursal::active()->orderBy('id')->first();
        }

        if ($sucursal === null) {
            throw new RuntimeException('No se encontró una sucursal activa/default para asignar empleados.');
        }

        return $sucursal;
    }

    private function vincularViri(): void
    {
        $empleado = DB::table('empleados')
            ->where('nombre', 'Ma. Viridiana')
            ->where('apellido_paterno', 'Gómez')
            ->where('apellido_materno', 'Fierros')
            ->first();

        if ($empleado === null) {
            return;
        }

        DB::table('users')
            ->where('name', 'viri')
            ->update([
                'empleado_id' => $empleado->id,
                'updated_at' => now(),
            ]);
    }

    private function nombre(string $nombreCompleto): string
    {
        $partes = explode(' ', $nombreCompleto);
        $nombre = array_shift($partes) ?? '';

        // Abreviaturas como "Ma." forman parte del nombre.
        while (isset($partes[0]) && str_ends_with($nombre, '.')) {
            $nombre .= ' '.array_shift($partes);
        }

        return $nombre;
    }

    private function apellidoPaterno(string $nombreCompleto): string
    {
        $partes = explode(' ', $nombreCompleto);
        $nombre = array_shift($partes) ?? '';

        while (isset($partes[0]) && str_ends_with($nombre, '.')) {
            $nombre .= ' '.array_shift($partes);
        }

        return array_shift($partes) ?? '';
    }

    private function apellidoMaterno(string $nombreCompleto): ?string
    {
        $partes = explode(' ', $nombreCompleto);
        $nombre = array_shift($partes) ?? '';

        while (isset($partes[0]) && str_ends_with($nombre, '.')) {
            $nombre .= ' '.array_shift($partes);
        }

        array_shift($partes); // paterno

        $materno = implode(' ', $partes);

        return $materno === '' ? null : $materno;
    }
}
