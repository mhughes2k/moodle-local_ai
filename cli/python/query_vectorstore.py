# This script is used to add a single "document" to the vectorstore, with the relevant metadata.
# It will also initialise the vectorstore if it doesn't exists

import argparse
import os
import textract
import pandas
import vectorstore

import json
from langchain_community.embeddings import HuggingFaceInstructEmbeddings
import langchain_community.vectorstores 
from langchain_community.vectorstores import FAISS
from langchain.text_splitter import CharacterTextSplitter
from langchain.docstore.document import Document

def main():
    parser = argparse.ArgumentParser(description='Add a document to the vectorstore.')
    parser.add_argument('--vectorstorelocation', type=str, help='Location of the vectorstore.')
    parser.add_argument('--query', type=str, help='Optional query')
    args = parser.parse_args()

    vectorstorelocation=args.vectorstorelocation
    cache_folder = vectorstorelocation+"cache/"
    model_name = "sentence-transformers/all-MiniLM-L6-v2"
    store_type = "faiss"
    query = args.query
    print(query)

    try:
        vs = vectorstore.get(store_type, vectorstorelocation, cache_folder, model_name)
    except ValueError as e:
        # Couldn't load VS so initialise with this document
        print(f"unable to load vector store {vectorstorelocation}: {e} ")
        return
    # Vector Store was OK
    if (query == ""):
        query = input("Enter your query: ")

    # docs = vs.similarity_search(query)
    results = vs.similarity_search_with_score(query)
    for result in results:
        # if ('metadata' in doc):
        doc = result[0];
        score = result[1]
        if 'title' in doc.metadata:
            title = doc.metadata['title']
            print(f"Title {title} - {score}")
        else :
            print("No title")
        # else:
            # print("No metadata")
            # print(doc)


if __name__ == '__main__':
    main()
