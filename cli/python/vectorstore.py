from langchain_community.embeddings import HuggingFaceInstructEmbeddings
from langchain_community.vectorstores import FAISS
import sys
# from openai import OpenAI
# from openai import OpenAIEmbeddings

# Get instance of VectorStore.
def eprint(*args, **kwargs):
    print(*args, file=sys.stderr, **kwargs)
def get(store_type, vectorstorelocation, cache_folder, model_name):
    try:
        if store_type == "faiss":
            #vectorstorelocation should end with a "/"
            eprint("Loading vectorstore from "+vectorstorelocation)
            if model_name == "ollama":
                vectorstore.load_local(vectorstorelocation,
                                       cache_folder=cache_folder,
                                       model_name="llama2"
                                       )
            elif model_name == "openai":
                # Not supported yet.
                vectorstore = None
            else:
                vectorstore = FAISS.load_local(vectorstorelocation,
                    HuggingFaceInstructEmbeddings(
                        cache_folder=cache_folder,
                        model_name=model_name
                    )
                )
            
    except:
        eprint("Failed to load vectorstore from "+vectorstorelocation)
        vectorstore = None
    # TODO Support other vector store types.
    if vectorstore is None:
        raise ValueError("Vector store not available.")

    return vectorstore
