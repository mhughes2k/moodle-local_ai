# This script is used to add a single "document" to the vectorstore, with the relevant metadata.
# It will also initialise the vectorstore if it doesn't exists

import argparse
import os
import textract
import pandas
import vectorstore
from langchain.embeddings import HuggingFaceInstructEmbeddings
from langchain.vectorstores import FAISS
from langchain.text_splitter import CharacterTextSplitter
from langchain.docstore.document import Document

def main():
    parser = argparse.ArgumentParser(description='Add a document to the vectorstore.')
    parser.add_argument('--vectorstorelocation', type=str, help='Location of the vectorstore.')
    args = parser.parse_args()

    vectorstorelocation=args.vectorstorelocation
    cache_folder = vectorstorelocation+"cache/"
    model_name = "sentence-transformers/all-MiniLM-L6-v2"
    store_type = "faiss"

    try:
        vs = vectorstore.get(store_type, vectorstorelocation, cache_folder, model_name)
    except ValueError as e:
        # Couldn't load VS so initialise with this document
        print(f"unable to load vector store {vectorstorelocation}: {e} ")
        return
    # Vector Store was OK

    query = input("Enter your query: ")
    docs = vs.similarity_search(query)

    print(docs)


if __name__ == '__main__':
    main()
