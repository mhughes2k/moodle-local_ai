from langchain.embeddings import HuggingFaceInstructEmbeddings
from langchain.vectorstores import FAISS
# Get instance of VectorStore.
def get(store_type, vectorstorelocation, cache_folder, model_name):
    if store_type == "faiss":
        #vectorstorelocation should end with a "/"
        print("Loading vectorstore from "+vectorstorelocation)
        try:
            vectorstore = FAISS.load_local(vectorstorelocation,
                HuggingFaceInstructEmbeddings(
                    cache_folder=cache_folder,
                    model_name=model_name
                )
            )
        except:
            print("Failed to load vectorstore from "+vectorstorelocation)
            vectorstore = None
    # TODO Support other vector store types.
    if vectorstore is None:
        raise ValueError("Vector store not available.")

    return vectorstore
