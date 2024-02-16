import argparse
import os
import textract
import pandas
import vectorstore
import langchain_community.embeddings
import langchain_community.vectorstores
import shutil

def create_vector_store(vectorstorelocation,
    store_type = "faiss"
    ):
    if store_type == "faiss":
        vectorstore = FAISS(vectorstorelocation)
    # TODO Support other vector store types.
    else:
        raise ValueError("Vector store type not supported: "+store_type)
        return None
    return vectorstore

def main():
    parser = argparse.ArgumentParser(description='Initialise the vectorstore.')
    parser.add_argument('-v', '--vectorstorelocation', type=str, help='Location of the vectorstore.')
    parser.add_argument('-d', '--delete', action='store_true', help='Remove existing vectorstore.')
    args = parser.parse_args()
    
    # Validate Arguments
    if (args.vectorstorelocation is None):
        print("You must provide a --vectorstorelocation")
        return

    vectorstorelocation=args.vectorstorelocation+"/"
    cache_folder = vectorstorelocation+"cache/"
    model_name = "sentence-transformers/all-MiniLM-L6-v2"
    store_type = "faiss"
    
    if (args.delete):
        print("Deleting existing vector store at "+vectorstorelocation)
        try:
            shutil.rmtree(vectorstorelocation)
        except:
            print("No existing vector store to delete")
        return

    vs = vectorstore.get(store_type, vectorstorelocation, cache_folder, model_name)

    if vs is None:
        raise ValueError("Vector store not available. You may need to add a document to it first")
    else:
        print("Vector store initialised.")

if __name__ == '__main__':
    main()
