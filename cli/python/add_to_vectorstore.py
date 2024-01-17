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
import json

def main():
    parser = argparse.ArgumentParser(description='Add a document to the vectorstore.')
    parser.add_argument('--vectorstorelocation', type=str, help='Location of the vectorstore.')
    parser.add_argument('--documentpath', type=str, help='Path to the document.')
    parser.add_argument('--documentmetadata', type=str, help='Metadata for the document in JSON format.')
    args = parser.parse_args()

    vectorstorelocation=args.vectorstorelocation+"/"
    cache_folder = vectorstorelocation+"cache/"
    model_name = "sentence-transformers/all-MiniLM-L6-v2"
    store_type = "faiss"

    # Input document details.
    documentpath=args.documentpath
    documentmetadata=args.documentmetadata  # This should be a JSON string.

    print("Preparing document")
    document = prepare_document(documentpath, documentmetadata)
    print(document)

    embeddings = HuggingFaceInstructEmbeddings(model_name="sentence-transformers/all-MiniLM-L6-v2")
    try:
        vs = vectorstore.get(store_type, vectorstorelocation, cache_folder, model_name)
    except:
        # Couldn't load VS so initialise with this document
        print("Initialising vector store")

        vs = FAISS.from_documents([document], embeddings)
        try:
            vs.save_local(vectorstorelocation)
        except Exception as e:
            print("Failed to save vector store")
            print(e)
        return
    # Vector Store was OK
    vs.add_documents([document])
    try:
        vs.save_local(vectorstorelocation)
    except Exception as e:
        print("Failed to save vector store")
        print(e)


def prepare_document(documentpath, documentmetadata):
    # "fix" headings
    heading = fix_filenames(documentpath)
    text = extract_text(documentpath)
    text_splitter = CharacterTextSplitter(
        chunk_size=500,
        chunk_overlap=100,
    )
    split_text = text_splitter.split_text(text)
    print(split_text)
    page_content = ' '.join(split_text)

    # Parse metadata
    jsondata = json.loads(documentmetadata)
    print(jsondata)

    document = Document(
        page_content=page_content,
        metadata=jsondata,
    )
    return document


def fix_filenames(documentpath):
    # TODO
    heading = documentpath
    heading = heading.replace('_', ' ')
    heading = heading.split('.')[0]
    heading = heading.title()
    heading = heading.replace('/', ': ')
    return heading

def extract_text(documentpath):
    filetype = documentpath.split('.')[-1]
    if filetype != "md":
        text = textract.process(documentpath).decode("utf-8")
    else:
        with open(documentpath, 'r') as f:
            text = f.read()
            f.close()
    return text

if __name__ == '__main__':
    main()
