import sys
import pandas as pd

if len(sys.argv) < 2:
    print("Debes indicar la ruta del archivo Excel")
    sys.exit(1)

archivo = sys.argv[1]

print(f"Leyendo archivo: {archivo}")

# Leer todas las hojas del Excel
hojas = pd.read_excel(archivo, sheet_name=None)

print("Hojas encontradas:")
for nombre_hoja, datos in hojas.items():
    print(f"- {nombre_hoja}: {len(datos)} registros")

print("Archivo leído correctamente")
