#! /bin/env python3
# This script is used to add a single "document" to the vectorstore, with the relevant metadata.
# It will also initialise the vectorstore if it doesn't exists

import argparse
import sys
import vectorstore

import json
from langchain_community.embeddings import HuggingFaceInstructEmbeddings
import langchain_community.vectorstores 
from langchain_community.vectorstores import FAISS
from langchain.text_splitter import CharacterTextSplitter
from langchain.docstore.document import Document

DEFAULT_MODEL="sentence-transformers/all-MiniLM-L6-v2"
DEFAULT_STORE="faiss"
def eprint(*args, **kwargs):
    print(*args, file=sys.stderr, **kwargs)

def main():
    parser = argparse.ArgumentParser(description='Add a document to the vectorstore.')
    parser.add_argument('--vectorstorelocation', type=str, help='Location of the vectorstore. No trailing slash',required=True)
    parser.add_argument('--modelname', type=str,help='Model to use. Default: '+ DEFAULT_MODEL, default=DEFAULT_MODEL)
    parser.add_argument('--storetype', type=str,help="Vector store type to use. Default: "+DEFAULT_STORE, default=DEFAULT_STORE)
    parser.add_argument('--query', type=str, help='Optional query. If not provided will prompt for query.')
    args = parser.parse_args()

    vectorstorelocation=args.vectorstorelocation+"/"
    cache_folder = vectorstorelocation+"cache/"
    model_name = args.modelname
    store_type = args.storetype
    query = args.query
    eprint(query)

    try:
        vs = vectorstore.get(store_type, vectorstorelocation, cache_folder, model_name)
    except ValueError as e:
        # Couldn't load VS so initialise with this document
        eprint(f"unable to load vector store {vectorstorelocation}: {e} ")
        return 1
    # Vector Store was OK
    if (query is None):
        query = input("Enter your query: ")

    docs = vs.similarity_search(query)
    results = []
    for doc in docs:
        out = "Title:" + doc.metadata['title'] +"; Content:" + doc.page_content.strip()
        print(out)
    
    # results = vs.similarity_search_with_score(query)
    # print(results)
    return
    for result in results:
        doc = result[0];
        score = result[1]
        if 'title' in doc.metadata:
            title = doc.metadata['title']
            print(f"Title {title} - {score}")
        else :
            print("No title")

if __name__ == '__main__':
    main()
