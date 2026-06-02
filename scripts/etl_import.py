import sys
import os
import pandas as pd

MAPEO_HOJAS = {
    "Dim_Cliente": "dim_cliente.csv",
    "Dim_Producto": "dim_producto.csv",
    "Dim_Tiempo": "dim_tiempo.csv",
    "Dim_Ubicacion": "dim_ubicacion.csv",
    "Fact_Ventas": "fact_ventas.csv",
    "Fact_Opiniones": "fact_opiniones.csv",
}

def limpiar_columnas(df):
    df.columns = [str(c).strip() for c in df.columns]
    return df

def limpiar_dataframe(df):
    df = limpiar_columnas(df)
    df = df.fillna("")
    return df

def procesar_excel(archivo, carpeta_salida):
    excel = pd.ExcelFile(archivo)

    for hoja in excel.sheet_names:
        if hoja not in MAPEO_HOJAS:
            print(f"Hoja ignorada: {hoja}")
            continue

        print(f"Procesando hoja: {hoja}")

        df = pd.read_excel(
            archivo,
            sheet_name=hoja,
            engine="openpyxl"
        )

        df = limpiar_dataframe(df)

        ruta_salida = os.path.join(carpeta_salida, MAPEO_HOJAS[hoja])

        df.to_csv(
            ruta_salida,
            index=False,
            encoding="utf-8"
        )

        print(f"Generado: {ruta_salida}")

def procesar_csv(archivo, carpeta_salida):
    ruta_salida = os.path.join(carpeta_salida, "datos.csv")

    df = pd.read_csv(archivo)
    df = limpiar_dataframe(df)

    df.to_csv(
        ruta_salida,
        index=False,
        encoding="utf-8"
    )

    print(f"CSV generado: {ruta_salida}")

def procesar_xml(archivo, carpeta_salida):
    ruta_salida = os.path.join(carpeta_salida, "datos.csv")

    df = pd.read_xml(archivo)
    df = limpiar_dataframe(df)

    df.to_csv(
        ruta_salida,
        index=False,
        encoding="utf-8"
    )

    print(f"XML generado: {ruta_salida}")

def main():
    if len(sys.argv) < 3:
        print("Uso: python3 etl_import.py archivo_entrada carpeta_salida")
        sys.exit(1)

    archivo = sys.argv[1]
    carpeta_salida = sys.argv[2]

    os.makedirs(carpeta_salida, exist_ok=True)

    extension = os.path.splitext(archivo)[1].lower()

    print(f"Archivo recibido: {archivo}")
    print(f"Carpeta de salida: {carpeta_salida}")

    if extension in [".xlsx", ".xls"]:
        procesar_excel(archivo, carpeta_salida)
    elif extension == ".csv":
        procesar_csv(archivo, carpeta_salida)
    elif extension == ".xml":
        procesar_xml(archivo, carpeta_salida)
    else:
        print("Formato no soportado")
        sys.exit(1)

    print("OK")

if __name__ == "__main__":
    main()